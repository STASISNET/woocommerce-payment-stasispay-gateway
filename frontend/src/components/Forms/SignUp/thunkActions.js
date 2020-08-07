import {baseUrl} from "../../../constants";
import {setFormType, setLoginErrors, setPasswordErrors} from "./actions";
import 'whatwg-fetch';


export const onSignUpButtonClick = () => {
    return (dispatch, getState) => {
        const state = getState();
        dispatch(setFormType("LOADING"));

        const email = state.signUp.login.value;
        const password = state.signUp.password.value;

        const body = new FormData();
        body.append('email', email)
        body.append('password', password);
        body.append('action', 'signup');

        window.fetch(window.WC_STASISPAY.url, {
            method: 'POST',
            body
        })
            .then(res => res.json())
            .then(response => {
                console.log(response);
                const { email, detail, password, redirect } = response;

                if (redirect) {
                    window.location = window.location;
                    window.location.reload();
                } else {
                    if (email) {
                        dispatch(setLoginErrors(email));
                    } else if (password) {
                        dispatch(setPasswordErrors(password));
                    } else if (detail) {
                        dispatch(setLoginErrors(detail));
                    }
                    dispatch(setFormType("INPUT"));
                }
            });
    }
}