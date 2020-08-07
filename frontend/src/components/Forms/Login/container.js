import { connect } from 'react-redux';

import {onLoginButtonClick} from "./thunkActions";
import {Login} from ".";

const mapStateToProps = (state) => {
    return {
        type: state.login.formType,
        isButtonDisabled: !state.login.login.value || !state.login.password.value
    }
}

const mapDispatchToProps = (dispatch) => {
    return {
        onLoginButtonClick: () => {
            dispatch(onLoginButtonClick());
        }
    }
}

export const LoginContainer = connect(
    mapStateToProps,
    mapDispatchToProps
)(Login);
