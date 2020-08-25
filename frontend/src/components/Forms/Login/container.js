import { connect } from 'react-redux';
import { Login } from '.';
import { onLoginButtonClick } from './thunkActions';

const mapStateToProps = (state) => {
    return {
        type: state.login.formType,
        upgrade: state.login.login.upgrade,
        isButtonDisabled:
            !state.login.login.value || !state.login.password.value,
    };
};

const mapDispatchToProps = (dispatch) => {
    return {
        onLoginButtonClick: () => {
            dispatch(onLoginButtonClick());
        },
    };
};

export const LoginContainer = connect(
    mapStateToProps,
    mapDispatchToProps
)(Login);
