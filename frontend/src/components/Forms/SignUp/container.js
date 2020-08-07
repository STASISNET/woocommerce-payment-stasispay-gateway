import {connect} from 'react-redux';

import {SignUp} from ".";
import {onSignUpButtonClick} from "./thunkActions.js";
import {clearForm, setFormType} from "./actions";

const mapStateToProps = (state) => {
    return {
        type: state.signUp.formType,
        email: state.signUp.login.value, 
        isButtonDisabled: !state.signUp.checkboxChecked || !state.signUp.login.value || !state.signUp.password.value
    }
}

const mapDispatchToProps = (dispatch) => {
    return {
        onSignUpButtonClick: () => {
            dispatch(onSignUpButtonClick());
        },
        onReturnButtonClick: () => {
            dispatch(setFormType("INPUT"));
        },
        clearForm: () => {
            dispatch(clearForm());
        }
    }
}

export const SignUpContainer = connect(
    mapStateToProps,
    mapDispatchToProps
)(SignUp);
