<?php
/*
 * Plugin Name: WooCommerce StasisPay Payment Gateway
 * Plugin URI: https://stasis.net
 * Description: Take credit card and EURS payments on your store.
 * Author: STASIS EURS team
 * Author URI: https://stasis.net
 * Version: 1.0.0
 */

 
if ( ! class_exists( 'WC_StasisPay' ) ) {

	/**
	 * WooCommerce EveryPay main class.
	 *
	 * @class   Everypy
	 * @version 1.0.0
	 */
	final class WC_StasisPay {

		/**
		 * Instance of this class.
		 *
		 * @access protected
		 * @access static
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Slug
		 *
		 * @access public
		 * @var    string
		 */
		public $gateway_slug = 'stasispay';

		/**
		 * Gateway name.
		 *
		 * @NOTE   Do not put WooCommerce in front of the name. It is already applied.
		 * @access public
		 * @var    string
		 */
		public $name = "Gateway StasisPay";

		/**
		 * Gateway version.
		 *
		 * @access public
		 * @var    string
		 */
		public $version = '1.0.0';

		/**
		 * The Gateway URL.
		 *
		 * @access public
		 * @var    string
		 */
		public $web_url = "https://stasis.net/";

		/**
		 * The Gateway documentation URL.
		 *
		 * @access public
		 * @var    string
		 */
		public $doc_url = "https://stasis.net/sellback/api/v2/docs/swagger/#/";

		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, "Clone isn't allowed", $this->version );
		}

		/**
		 * Disable unserializing of the class
		 *
		 * @since  1.0.0
		 * @access public
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, "Wakeup isn't allowed", $this->version );
		}

		/**
		 * Initialize the plugin public actions.
		 *
		 * @access private
		 */
		private function __construct() {
			// Hooks.
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
			// add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			// Is WooCommerce activated?
			if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );

				return false;
			} else {
				// Check we have the minimum version of WooCommerce required before loading the gateway.
				if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '4.0', '>=' ) ) {
					if ( class_exists( 'WC_Payment_Gateway' ) ) {

						$this->includes();

						add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
						/*
									add_filter( 'woocommerce_currencies', array( $this, 'add_currency' ) );
									add_filter( 'woocommerce_currency_symbol', array( $this, 'add_currency_symbol' ), 10, 2 );
						*/
					}
				} else {
					add_action( 'admin_notices', array( $this, 'upgrade_notice' ) );

					return false;
				}
			}
		}

		/**
		 * Plugin action links.
		 *
		 * @access public
		 *
		 * @param  mixed $links
		 *
		 * @return mixed $links
		 */
		public function action_links( $links ) {
			if ( current_user_can( 'manage_woocommerce' ) ) {
				$plugin_links = array(
					'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $this->gateway_slug ) . '">' . __( 'Payment Settings', 'stasispay' ) . '</a>',
				);

				return array_merge( $plugin_links, $links );
			}

			return $links;
		}

		/**
		 * Plugin row meta links
		 *
		 * @access public
		 *
		 * @param  array $input already defined meta links
		 * @param  string $file plugin file path and name being processed
		 *
		 * @return array $input
		 */
		public function plugin_row_meta( $input, $file ) {
			if ( plugin_basename( __FILE__ ) !== $file ) {
				return $input;
			}

			$links = array(
				'<a href="' . esc_url( $this->doc_url ) . '"> Documentation </a>',
			);

			$input = array_merge( $input, $links );

			return $input;
		}

		/**
		 * Include files.
		 *
		 * @access private
		 * @return void
		 */
		private function includes() {
			require_once( 'includes/class-wc-gateway-stasispay.php' );

			// This supports the plugin extensions 'WooCommerce Subscriptions' and 'WooCommerce Pre-orders'.
			/*
				  if( class_exists( 'WC_Subscriptions_Order' ) || class_exists( 'WC_Pre_Orders_Order' ) ) {
					include_once( 'includes/class-wc-gateway-' . str_replace( '_', '-', $this->gateway_slug ) . '-add-ons.php' );
				  }
			*/
		}

		/**
		 * This filters the gateway to only supported countries.
		 *
		 * @access public
		 */
		/*
			public function gateway_country_base() {
			  return apply_filters( 'woocommerce_gateway_country_base', array( 'EE', 'US', 'UK', 'FR' ) );
			}
		*/

		/**
		 * Add the gateway.
		 *
		 * @access public
		 *
		 * @param  array $methods WooCommerce payment methods.
		 *
		 * @return array WooCommerce gateway.
		 */
		public function add_gateway( $methods ) {
			$methods[] = 'WC_StasisPay_Gateway';

			return $methods;
		}

		/**
		 * WooCommerce Fallback Notice.
		 *
		 * @access public
		 * @return string
		 */
		public function woocommerce_missing_notice() {
			echo '<div class="error woocommerce-message wc-connect"><p>' . sprintf( __( 'Sorry, <strong>WooCommerce %s</strong> requires WooCommerce to be installed and activated first. Please install <a href="%s">WooCommerce</a> first.', $this->text_domain ), $this->name, admin_url( 'plugin-install.php?tab=search&type=term&s=WooCommerce' ) ) . '</p></div>';
		}

		/**
		 * WooCommerce Payment Gateway Upgrade Notice.
		 *
		 * @access public
		 * @return string
		 */
		public function upgrade_notice() {
			echo '<div class="updated woocommerce-message wc-connect"><p>' . sprintf( __( 'WooCommerce %s depends on version 2.2 and up of WooCommerce for this gateway to work! Please upgrade before activating.', 'payment-gateway-stasispay' ), $this->name ) . '</p></div>';
		}

		/** Helper functions ******************************************************/

		/**
		 * Get the plugin url.
		 *
		 * @access public
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @access public
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

	} // end if class

	add_action( 'plugins_loaded', array( 'WC_StasisPay', 'get_instance' ), 0 );

} // end if class exists.

/**
 * Returns the main instance of WC_StasisPay to prevent the need to use globals.
 *
 * @return WooCommerce StasisPay
 */
function WC_StasisPay() {
	return WC_StasisPay::get_instance();
}