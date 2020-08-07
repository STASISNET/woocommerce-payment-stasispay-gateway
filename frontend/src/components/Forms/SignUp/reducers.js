import {
    CHANGE_SIGNUP_LOGIN_VALUE,
    CHANGE_SIGNUP_PASSWORD_VALUE,
    CLEAR_FORM,
    SET_FORM_TYPE,
    SET_LOGIN_ERRORS,
    SET_PASSWORD_ERRORS,
    TOGGLE_CHECKBOX
} from "./actions";

const initialState = {
    login: {
        value: ''
    },
    password: {
        value: ''
    },
    formType: "INPUT"    
};

export const signUpReducer = function(state = initialState, action) {
    switch (action.type) {
        case CHANGE_SIGNUP_LOGIN_VALUE: {
            return {
                ...state,
                login: {
                    value: action.payload.value,
                    errors: undefined
                }
            };
        }

        case SET_LOGIN_ERRORS: {
            return {
                ...state,
                login: {
                    ...state.login,
                    errors: action.payload.errors
                }
            }
        }

        case CHANGE_SIGNUP_PASSWORD_VALUE: {
            return {
                ...state,
                password: {
                    value: action.payload.value,
                    errors: undefined
                }
            }
        }

        case SET_PASSWORD_ERRORS: {
            return {
                ...state,
                password: {
                    ...state.password,
                    errors: action.payload.errors
                }
            }
        }

        case CLEAR_FORM: {
            return {
                ...state,
                password: {
                    value: ''
                },
                login: {
                    value: ''
                },
                formType: "INPUT"
            }
        }

        case SET_FORM_TYPE: {
            return {
                ...state,
                formType: action.payload.type
            }
        }
        
        case TOGGLE_CHECKBOX: {
            return {
                ...state,
                checkboxChecked: !state.checkboxChecked
            }
        }

        default:
            return state;
    }
}
