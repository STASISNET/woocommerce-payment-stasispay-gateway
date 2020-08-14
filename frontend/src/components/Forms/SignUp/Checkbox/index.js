import * as React from 'react';
import {Checkbox as CheckboxBase, FormControlLabel} from '@material-ui/core';
import './Checkbox.css';
import {termsUrl} from "../../../../constants";


export const Checkbox = React.memo(({isChecked, onToggleValue}) => {
    return (
        <div className={'checkbox'}>
            <FormControlLabel
                control={
                    <CheckboxBase
                        color={"primary"}
                        onChange={onToggleValue}
                        checked={isChecked}
                    />
                }
                label={<div className={'checkbox__text'}>I accept <a href={termsUrl} target={"_blank"}>Terms of Business</a></div>}
            />
        </div>
    )
})

export default Checkbox;