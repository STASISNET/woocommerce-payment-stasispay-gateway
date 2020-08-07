import { connect } from 'react-redux';
import {setLoginValue} from "../actions";
import {LoginInput} from ".";

const mapStateToProps = (state) => {
    return {
        value: state.login.login.value,
        error: state.login.login.errors
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
