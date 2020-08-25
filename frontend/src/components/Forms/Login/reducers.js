import {
    CHANGE_LOGIN_VALUE,
    CHANGE_PASSWORD_VALUE,
    SET_FORM_TYPE,
    SET_LOGIN_ERRORS,
    SET_PASSWORD_ERRORS,
    SET_UPGRADE_MESSAGE,
} from './actions';

const initialState = {
    login: {
        value: '',
        upgrade: false,
    },
    password: {
        value: '',
    },
    formType: 'INPUT',
};

export const loginReducer = function (state = initialState, action) {
    switch (action.type) {
        case CHANGE_LOGIN_VALUE: {
            return {
                ...state,
                login: {
                    value: action.payload.value,
                    errors: undefined,
                    upgrade: false,
                },
            };
        }

        case SET_LOGIN_ERRORS: {
            return {
                ...state,
                login: {
                    ...state.login,
                    errors: action.payload.errors,
                },
            };
        }

        case SET_UPGRADE_MESSAGE: {
            return {
                ...state,
                login: {
                    ...state.login,
                    upgrade: action.payload.enabled,
                },
            };
        }

        case CHANGE_PASSWORD_VALUE: {
            return {
                ...state,
                password: {
                    value: action.payload.value,
                    errors: undefined,
                },
            };
        }

        case SET_PASSWORD_ERRORS: {
            return {
                ...state,
                password: {
                    ...state.password,
                    errors: action.payload.errors,
                },
            };
        }

        case SET_FORM_TYPE: {
            return {
                ...state,
                formType: action.payload.type,
            };
        }

        default:
            return state;
    }
};
