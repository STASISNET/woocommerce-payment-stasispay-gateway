import { connect } from 'react-redux';
import {setLoginValue} from "../actions";
import {LoginInput} from ".";

const mapStateToProps = (state) => {
    return {
        value: state.signUp.login.value,
        error: state.signUp.login.errors
    }
}

const mapDispatchToProps = (dispatch) => {
    return {
        onChange: (value) => {
            dispatch(setLoginValue(value));
        }
    }
}

export const LoginContainer = connect(
    mapStateToProps,
    mapDispatchToProps
)(LoginInput);
