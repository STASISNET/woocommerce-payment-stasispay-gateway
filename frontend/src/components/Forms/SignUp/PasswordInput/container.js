import { connect } from 'react-redux';
import { PasswordInput} from ".";
import {setPasswordValue} from "../actions";

const mapStateToProps = (state) => {
    return {
        value: state.signUp.password.value,
        error: state.signUp.password.errors
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
