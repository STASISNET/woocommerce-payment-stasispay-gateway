import 'whatwg-fetch';
import { setFormType, setLoginErrors, setUpgradeMessage } from './actions';

export const onLoginButtonClick = () => {
    return (dispatch, getState) => {
        const state = getState();
        dispatch(setFormType('LOADING'));

        const email = state.login.login.value;
        const password = state.login.password.value;

        const body = new FormData();
        body.append('email', email);
        body.append('password', password);
        body.append('action', 'login');

        window
            .fetch(window.WC_STASISPAY.url, {
                method: 'POST',
                body,
            })
            .then((res) => res.json())
            .then((response) => {
                console.log(response);
                const { detail, redirect, upgrade } = response;
                if (redirect) {
                    window.location = window.location;
                    window.location.reload();
                } else {
                    if (detail) {
                        dispatch(setLoginErrors(detail));
                    }
                    if (upgrade) {
                        dispatch(setUpgradeMessage(true));
                    }
                    dispatch(setFormType('INPUT'));
                }
            });
    };
};
