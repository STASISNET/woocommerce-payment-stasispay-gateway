
export const CHANGE_SIGNUP_LOGIN_VALUE = 'signup/CHANGE_SIGNUP_LOGIN_VALUE';

export const setLoginValue = (value) => ({
    type: CHANGE_SIGNUP_LOGIN_VALUE,
    payload: {
        value
    }
});

export const CHANGE_SIGNUP_PASSWORD_VALUE = 'signup/CHANGE_SIGNUP_PASSWORD_VALUE';

export const setPasswordValue = (value) => ({
    type: CHANGE_SIGNUP_PASSWORD_VALUE,
    payload: {
        value
    }
})

export const SET_FORM_TYPE = 'signup/SET_FORM_TYPE';

export const setFormType = (type) => ({
    type: SET_FORM_TYPE,
    payload: {
        type
    }
});

export const CLEAR_FORM = 'signup/CLEAR_FORM';

export const clearForm = () => ({
    type: CLEAR_FORM
})

export const SET_LOGIN_ERRORS = 'signup/SET_LOGIN_ERRORS';

export const setLoginErrors = (errors) => ({
    type: SET_LOGIN_ERRORS,
    payload: {
        errors
    }
});

export const SET_PASSWORD_ERRORS = 'signup/SET_PASSWORD_ERRORS';

export const setPasswordErrors = (errors) => ({
    type: SET_PASSWORD_ERRORS,
    payload: {
        errors
    }
})

export const TOGGLE_CHECKBOX = 'TOGGLE_CHECKBOX';

export const toggleCheckbox = () => ({
    type: TOGGLE_CHECKBOX
})
