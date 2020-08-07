import { connect } from 'react-redux';
import { PasswordInput} from ".";
import {setPasswordValue} from "../actions";

const mapStateToProps = (state) => {
    return {
        value: state.login.password.value,
        error: state.login.password.errors
    }
}

const mapDispatchToProps = (dispatch) => {
    return {
        onChange: (value) => {
            dispatch(setPasswordValue(value));
        }
    }
}

export const PasswordContainer = connect(
    mapStateToProps,
    mapDispatchToProps
)(PasswordInput);
