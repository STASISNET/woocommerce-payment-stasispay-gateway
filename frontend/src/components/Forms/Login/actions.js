export const CHANGE_LOGIN_VALUE = 'login/CHANGE_LOGIN_VALUE';

export const setLoginValue = (value) => ({
    type: CHANGE_LOGIN_VALUE,
    payload: {
        value,
    },
});

export const CHANGE_PASSWORD_VALUE = 'login/CHANGE_PASSWORD_VALUE';

export const setPasswordValue = (value) => ({
    type: CHANGE_PASSWORD_VALUE,
    payload: {
        value,
    },
});

export const SET_FORM_TYPE = 'login/SET_FORM_TYPE';

export const setFormType = (type) => ({
    type: SET_FORM_TYPE,
    payload: {
        type,
    },
});

export const SET_LOGIN_ERRORS = 'login/SET_LOGIN_ERRORS';

export const setLoginErrors = (errors) => ({
    type: SET_LOGIN_ERRORS,
    payload: {
        errors,
    },
});

export const SET_PASSWORD_ERRORS = 'login/SET_PASSWORD_ERRORS';

export const setPasswordErrors = (errors) => ({
    type: SET_PASSWORD_ERRORS,
    payload: {
        errors,
    },
});

export const SET_UPGRADE_MESSAGE = 'login/SET_UPGRADE_MESSAGE';

export const setUpgradeMessage = (enabled) => ({
    type: SET_UPGRADE_MESSAGE,
    payload: {
        enabled,
    },
});
