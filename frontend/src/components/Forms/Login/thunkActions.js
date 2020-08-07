import {baseUrl} from "../../../constants";
import {setFormType, setLoginErrors, setPasswordErrors} from "./actions";
import 'whatwg-fetch';

export const onLoginButtonClick = () => {
    return (dispatch, getState) => {
        const state = getState();
        dispatch(setFormType("LOADING"));

        const email = state.login.login.value;
        const password = state.login.password.value;

        const body = new FormData();
        body.append('email', email)
        body.append('password', password);
        body.append('action', 'login');

        window.fetch(window.WC_STASISPAY.url, {
            method: 'POST',
            body
        })
            .then(res => res.json())
            .then(response => {
                console.log(response);
                const { detail, redirect } = response;
                if (redirect) {
                    window.location = window.location;
                    window.location.reload();
                } else {
                    if (detail) {
                        dispatch(setLoginErrors(detail));
                    }
                    dispatch(setFormType("INPUT"));
                }
            });
    }
}
