<?php
/*
 * Plugin Name: WooCommerce StasisPay Payment Gateway
 * Plugin URI: https://stasis.net
 * Description: Accept credit card and EURS payments on your store.
 * Author: STASIS EURS team
 * Author URI: https://stasis.net
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly.

class APIException extends Exception
{
    public function __construct($data)
    {
        $this->data = $data;
        parent::__construct();
    }
}

class WC_StasisPay_Gateway extends WC_Payment_Gateway
{

    /**
     * Class constructor, more about it in Step 3
     */
    public function __construct()
    {
        $this->id = 'stasispay'; // payment gateway plugin ID
        $this->icon = apply_filters('woocommerce_gateway_stasis_icon', plugins_url('/assets/images/stasis.png', dirname(__FILE__)));
        $this->has_fields = true; // in case you need a custom credit card form
        $this->method_title = 'StasisPay Gateway';
        $this->method_description = 'Take payment with credit cards and EURS'; // will be displayed on the options page

        // gateways can support subscriptions, refunds, saved payment methods
        $this->supports = array(
            'products'
        );

        // Method with all the options fields
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->payment_form = 'iframe';
        $this->testmode = 'yes' === $this->get_option('testmode');
        $this->api_endpoint = $this->testmode === false ? 'https://stasis.net/sellback/api/v2/' : 'https://stage.stasis.net/sellback/api/v2/';
        $this->private_key = $this->testmode ? $this->get_option('test_private_key') : $this->get_option('private_key');
        $this->public_key = $this->testmode ? $this->get_option('test_public_key') : $this->get_option('public_key');
        $this->eth_address = $this->get_option('eth_address');

        // Log is created always for main transaction points - debug option adds more logging points during transaction
        $this->debug = $this->get_option('debug');
        $this->log   = new WC_Logger();

        // Hooks
        if (is_admin()) {
            add_action('admin_notices', array($this, 'checks'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
                $this,
                'process_admin_options'
            ));
        }
        // Receipt page creates POST to gateway or hosts iFrame
        add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));

        // Add returning user / callback handler to WC API
        add_action(
            'woocommerce_api_wc_gateway_' . $this->id,
            array($this, 'stasispay_return_handler')
        );
    }

    protected function make_api_request($url, $args, $auth_token = null, $method = "POST")
    {
        $headers = array(
            'Content-Type' => "application/json"
        );

        if ($args) {
            $body =  wp_json_encode($args);
            $hmac = $this->sign_gateway_request($body);

            $headers['X-API-Partner'] = $this->public_key . ':' . $hmac;
        } else {
            $body = null;
        }

        if ($auth_token) {
            $headers['Authorization'] = "Bearer " . $auth_token;
        }

        $pload = array(
            'method' => $method,
            'blocking' => true,
            'headers' => $headers,
            'body' => $body,
            'data_format' => 'body',
            'cookies' => array(),
            'timeout' => 60
        );

        $api_response = wp_remote_post($this->api_endpoint . $url, $pload);

        if (!is_wp_error($api_response)) {
            $data = json_decode(wp_remote_retrieve_body($api_response));

            if ($api_response['response']['code'] >= 400) {
                throw new APIException($data);
            } else {
                return $data;
            }
        } else {
            throw new Exception($api_response->get_error_messages());
        }
    }

    public function stasispay_return_handler()
    {
        ob_start();

        $post_response = ! empty($_POST) ? $_POST : false;
        if ($post_response && isset($_POST['action'])) {
            $action = $_POST['action'];

            $args = array(
                "email" => $_POST['email'],
                "password" => $_POST['password']
            );

            switch ($action) {
                case "signup":
                    try {
                        $this->make_api_request("auth/signup/", $args);
                    } catch (APIException $e) {
                        $resp = array("redirect" => false);
                        if (isset($e->data->non_field_errors)) {
                            $resp['detail'] = $e->data->non_field_errors;
                        }
                        if (isset($e->data->email)) {
                            $resp['email'] = $e->data->email;
                        }
                        if (isset($e->data->password)) {
                            $resp['password'] = $e->data->password;
                        }
                        wp_send_json($resp);
                        wp_die();
                    }

                    try {
                        $data = $this->make_api_request('auth/token/', $args);
                        $auth_token = $data->token;

                        $this->make_api_request(
                            'verification/',
                            array(
                                "verification_type" => "one_step",
                                "user_type" => "individual"
                            ),
                            $auth_token,
                            "PATCH"
                        );

                        $this->make_api_request(
                            'verification/documents/sign/',
                            array("type" => "gozo_disclaimer"),
                            $auth_token
                        );

                        $this->make_api_request("verification/submit/", null, $auth_token);

                        WC()->session->set('stasispay-auth-token', $auth_token);

                        do {
                            sleep(3);
                            $verification_data =
                                $this->make_api_request('verification/', null, $auth_token, "GET");
                        } while ($verification_data->state != "ready");

                        $success = true;
                        $detail = null;
                    } catch (Exception $e) {
                        $detail = var_dump($e->data);
                        $success = false;
                    }

                    wp_send_json(array(
                        "detail" => $detail,
                        "redirect" => $success
                    ));
                    wp_die();

                case "login":
                    $detail = "";
                    $success = true;

                    try {
                        $data = $this->make_api_request('auth/token/', $args);
                        $auth_token = $data->token;
                    } catch (Exception $e) {
                        wp_send_json(array(
                            "detail" => $e->data->detail,
                            "redirect" => false
                        ));
                        wp_die();
                    }

                    try {
                        $data = $this->make_api_request('verification/', null, $auth_token, "GET");

                        if ($data->state == "not_sent") {
                            $this->make_api_request(
                                'verification/',
                                array(
                                "verification_type" => "one_step",
                                "user_type" => "individual"
                            ),
                                $auth_token,
                                "PATCH"
                            );

                            $this->make_api_request(
                                'verification/documents/sign/',
                                array("type" => "gozo_disclaimer"),
                                $auth_token
                            );

                            $this->make_api_request("verification/submit/", null, $auth_token);
                        }

                        WC()->session->set('stasispay-auth-token', $auth_token);
                    } catch (Exception $e) {
                        $detail = var_dump($e->data);
                        $success = false;
                    }

                    wp_send_json(array(
                        "detail" => $detail,
                        "redirect" => $success
                    ));
                    wp_die();
                }
        } else {
            $response = ! empty($_GET) ? $_GET : false;
            if ($response && isset($_GET['key'])) {
                header('HTTP/1.1 200 OK');

                $key = $_GET['key'];

                $order_id = wc_get_order_id_by_order_key($key);
                $order = wc_get_order($order_id);
                if (!$order) {
                    wp_die("Request Failure: order not found", "StasisPay", array( 'response' => 200 ));
                }

                if (isset($_GET['action']) && ($_GET['action'] === 'logout')) {
                    WC()->session->set('stasispay-auth-token', null);
                    $redirect_url = $order->get_checkout_payment_url(true);
                    wp_redirect($redirect_url);
                # wp_die();
                } else {
                    if (!$order->is_editable()) {
                        $redirect_url = $this->get_return_url($order);
                        wp_redirect($redirect_url);
                    }

                    $tx = $order->get_transaction_id();
                    $token = get_post_meta($order_id, '_customer_token', true);

                    if (!$token) {
                        wp_die("Request Failure: please contact merchant to complete order manually", "StasisPay", array( 'response' => 200 ));
                    }

                    $info = $this->make_api_request("pipeline/transactions/?card_payment_id=" . $tx, null, $token, "GET");

                    if (isset($info->results[0])) {
                        $txdata = $info->results[0];
                        $status = $txdata->pipeline_status;

                        $order->payment_complete($tx);
                        // Add order note
                        $order->add_order_note(
                            sprintf('Card payment was successfully processed by StasisPay (Reference: %s, Timestamp: %s)', $tx, $txdata->datetime)
                        );
                        // Remove cart
                        WC()->cart->empty_cart();

                        $this->log->add($this->id, 'Order complete');
                        $redirect_url = $this->get_return_url($order);
                        wp_redirect($redirect_url);

                    #wp_die();
                    } else {
                        $html = "<script>setTimeout(function() {window.location = window.location;}, 5000);</script>";
                        $html .= "<div>Waiting for transaction...<br/>It shouldn't take more than 1 minute.</div>";

                        # echo $html;
                        wp_die($html, 'StasisPay');
                    }
                }
            } else {
                wp_die("Request Failure", "StasisPay", array( 'response' => 200 ));
            }
        }
    }


    public function checks()
    {
        if ($this->enabled == 'no') {
            return;
        }

        // PHP Version.
        if (version_compare(phpversion(), '5.3', '<')) {
            echo '<div class="error" id="wc_stasispay_notice_phpversion"><p>' . sprintf(__('StasisPay Error: StasisPay requires PHP 5.3 and above. You are using version %s.', 'stasispay'), phpversion()) . '</p></div>';
        } // Check required fields.
        elseif (!$this->public_key || !$this->private_key) {
            if ($this->testmode === false) {
                echo '<div class="error" id="wc_stasispay_notice_credentials"><p>' . __('StasisPay Error: Please enter your public and private API keys.', 'stasispay') . '</p></div>';
            } else {
                echo '<div class="error" id="wc_stasispay_notice_credentials"><p>' . __('StasisPay Error: Please enter your public and private TEST API keys.', 'stasispay') . '</p></div>';
            }
        } elseif (!$this->eth_address) {
            echo '<div class="error" id="wc_stasispay_notice_credentials"><p>' . __('StasisPay Error: Please enter your Ethereum address.', 'stasispay') . '</p></div>';
        }
        // warn about test payments
        if ($this->testmode === true) {
            echo '<div class="update-nag" id="wc_stasispay_notice_sandbox"><p>' . __("StasisPay payment gateway is in test mode, real payments not processed!", 'stasispay') . '</p></div>';
        }

        // warn about unsecure use if: iFrame in use and either WC force SSL or plugin with same effect is not active (borrowing logics from Stripe)
        if (($this->payment_form === 'iframe') && (get_option('woocommerce_force_ssl_checkout') === 'no') && !class_exists('WordPressHTTPS')) {
            echo '<div class="error" id="wc_stasispay_notice_ssl"><p>' . sprintf(__('StasisPay iFrame mode is enabled, but your checkout is not forced to use HTTPS. While StasisPay iFrame remains secure users may feel insecure due to missing confirmation in browser address bar. Please <a href="%s">enforce SSL</a> and ensure your server has a valid SSL certificate!', 'stasispay'), admin_url('admin.php?page=wc-settings&tab=advanced')) . '</p></div>';
        }

        if (get_woocommerce_currency() != 'EUR') {
            echo '<div class="error" id="wc_stasispay_notice_sandbox"><p>' . __("StasisPay works only with EUR as a currency at the moment!", 'stasispay') . '</p></div>';
        }
    }

    public function is_available()
    {
        if ($this->enabled == 'no') {
            return false;
        }

        if (!$this->public_key || !$this->private_key) {
            return false;
        }

        if (get_woocommerce_currency() != 'EUR') {
            return false;
        }

        return true;
    }

    /**
     * Plugin options, we deal with it in Step 3 too
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'       => 'Enable/Disable',
                'label'       => 'Enable StasisPay Gateway',
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ),
            'title' => array(
                'title'       => 'Title',
                'type'        => 'text',
                'description' => 'This controls the title which the user sees during checkout.',
                'default'     => 'Credit Card',
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => 'Description',
                'type'        => 'textarea',
                'description' => 'This controls the description which the user sees during checkout.',
                'default'     => 'Pay with your credit card via StasisPay payment gateway.',
            ),
            'eth_address' => array(
                'title'       => 'Ethereum address to receive EURS',
                'type'        => 'text',
                'description' => "<b>Shouldn't</b> be an address on Gozo.pro exchange or STASIS sellback due to regulation.<br/>Can be an address from the <a href='https://eurs.stasis.net/wallet/' target='_blank'>STASIS wallet</a>.",
            ),
            'testmode' => array(
                'title'       => 'Test mode',
                'label'       => 'Enable Test Mode',
                'type'        => 'checkbox',
                'description' => 'Place the payment gateway in test mode using test API keys.',
                'default'     => 'yes',
                'desc_tip'    => true,
            ),
            'test_public_key' => array(
                'title'       => 'Test Public Key',
                'type'        => 'text'
            ),
            'test_private_key' => array(
                'title'       => 'Test Private Key',
                'type'        => 'password',
            ),
            'public_key' => array(
                'title'       => 'Live Public Key',
                'type'        => 'text'
            ),
            'private_key' => array(
                'title'       => 'Live Private Key',
                'type'        => 'password'
            ),
            'debug'         =>  array(
                'title'       => 'Debug',
                'label'       => 'Enable Debug',
                'type'        => 'checkbox',
                'description' => 'Log debug transaction messages.',
                'default'     => 'yes',
            )
        );
    }

    public function validate_text_field($key, $value = null)
    {
        if (in_array($key, array('eth_address', 'test_public_key', 'test_private_key', 'public_key', 'private_key'))) {
            $field = $this->get_field_key($key);
            if (isset($_POST[$field])) {
                $value = trim(wp_strip_all_tags(stripslashes($_POST[$field])));
            }
            return $value;
        } else {
            return parent::validate_text_field($key, $value);
        }
    }

    /*
         * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
         */
    public function payment_scripts()
    {
        // if our payment gateway is disabled, we do not have to enqueue JS too
        if ('no' === $this->enabled) {
            return;
        }

        // no reason to enqueue JavaScript if API keys are not set
        if (empty($this->private_key) || empty($this->public_key)) {
            return;
        }

        // do not work with card detailes without SSL unless your website is in a test mode
        if (!$this->testmode && !is_ssl()) {
            return;
        }

        # wp_register_script('form-runtime-main', plugins_url('/frontend/build/static/js/runtime-main.js', dirname(__FILE__)), false, false, true);
        wp_register_script('form-main-chunk', plugins_url('/frontend/build/static/js/main.chunk.js', dirname(__FILE__)), array("form-2-chunk"), false, true);
        wp_register_script('form-2-chunk', plugins_url('/frontend/build/static/js/2.chunk.js', dirname(__FILE__)), false, false, true);

        wp_register_style('form-styles', plugins_url('/frontend/build/static/css/main.chunk.css', dirname(__FILE__)));

        # wp_enqueue_script('form-runtime-main');
        wp_enqueue_script('form-2-chunk');
        wp_enqueue_script('form-main-chunk');
        wp_enqueue_style("form-styles");
    }

    /*
         * We're processing the payments here, everything about it is in Step 5
         */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        if ($this->debug == 'yes') {
            $this->log->add($this->id, 'StasisPay selected for order #' . $order_id);
        }

        return array(
            'result'   => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        );
    }

    public function receipt_page($order_id)
    {
        $order = wc_get_order($order_id);
        $args  = $this->get_request_args($order);

        $this->payment_scripts();

        $token = WC()->session->get("stasispay-auth-token");
        if ($token) {
            try {
                $this->make_api_request("auth/token/verify/", array("token" => $token));
            } catch (Exception $e) {
                $token = null;
            }
        }

        if ($token) {
            echo $this->generate_iframe_form_html($args, $order, $token);
        } else {
            echo wpautop('To ensure security of your payment, sign up for a STASIS account or log in to your existing account.');

            wp_localize_script(
                'form-main-chunk',
                'WC_STASISPAY',
                array(
                    "url" => WC() -> api_request_url('wc_gateway_' . $this->id)
                )
            );

            wc_get_template(
                'card-form.html',
                $args,
                '',
                WC_StasisPay()::get_instance()->plugin_path() . '/templates/'
            );
        }

        #
    }


    protected function get_request_args($order)
    {
        $args = array(
            'amount'            => number_format($order->get_total(), 2, '.', ''),
            'success_url'      => WC() -> api_request_url('wc_gateway_' . $this->id) . "?key=" . $order->get_order_key(),
            'failure_url'      => wc_get_checkout_url(),
            'text'              => "Payment for order #" . $order->get_id(),
            'eth_address'       => $this->eth_address
        );

        ksort($args);

        if ($this->debug == 'yes') {
            $this->log->add($this->id, 'StasisPay payment request prepared and signed. ' . print_r($args, true));
        }

        return $args;
    }

    protected function sign_gateway_request($request)
    {
        return hash_hmac('sha256', $request, $this->private_key);
    }


    protected function generate_iframe_form_html($args, $order, $token)
    {
        $html         = '';
        $cancel_style = '';

        try {
            $data = $this->make_api_request("payment/", $args, $token);
        } catch (Exception $e) {
            if (isset($e->data->amount) && ($e->data->amount[0] === "Limit is exceeded")) {
                $html .= '<span>Your €250 limit within individual STASIS Pay account is exceeded!</span><br/>';
                $html .= '<span>Please proceed to <a href="https://stasis.net/sellback/" target="_blank">STASIS</a> to complete Full verification, ';
                $html .= 'or use <a href="' . WC() -> api_request_url('wc_gateway_' . $this->id) . "?action=logout&key=" . $order->get_order_key() . '">another account</a>.';
            } else {
                $html .= var_dump($e->data);
            }
            return $html;
        }

        $html .= '<iframe src="' . $data->url . '" id="wc_stasispay_iframe" name="wc_stasispay_iframe" width="358" height="489" style="border: 0;"></iframe>' . PHP_EOL;
        $html .= '</div>' . PHP_EOL;

        $html .= '<div id="wc_stasispay_iframe_buttons">' . PHP_EOL;

        $html .= '<a href="' . esc_url($order->get_cancel_order_url()) . '" id="wc_stasispay_iframe_cancel" class="button cancel" ' . $cancel_style . '>'
            . apply_filters('wc_stasispay_iframe_cancel', 'Cancel order') . '</a> ';
        $html .= '<a href="' . esc_url(wc_get_checkout_url()) . '" id="wc_stasispay_iframe_retry" class="button alt" style="display: none;">'
            . apply_filters('wc_stasispay_iframe_retry', 'Try paying again') . '</a>' . PHP_EOL;
        $html .= '</div>' . PHP_EOL;

        update_post_meta($order->get_id(), '_transaction_id', $data->card_payment_id);
        update_post_meta($order->get_id(), '_customer_token', $token);

        return $html;
    }
}