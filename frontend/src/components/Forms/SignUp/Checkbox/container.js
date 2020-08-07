import { connect } from 'react-redux';
import { Checkbox } from ".";
import { toggleCheckbox } from "../actions";

const mapStateToProps = (state) => {
    return {
        isChecked: state.signUp.checkboxChecked
    }
}

const mapDispatchToProps = (dispatch) => {
    return {
        onToggleValue: () => {
            dispatch(toggleCheckbox());
        }
    }
}

export const CheckboxContainer = connect(
    mapStateToProps,
    mapDispatchToProps
)(Checkbox);
