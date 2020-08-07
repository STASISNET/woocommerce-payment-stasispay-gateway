import * as React from 'react';
import {TextFieldDecorator} from "../../../../ui/TextFieldDecorator/TextFieldDecorator";
import {useCallback} from "react";


export const PasswordInput = React.memo(props => {
    const { value, error, onChange } = props;

    const onChangeCallback = useCallback((event) => {
        onChange(event.target.value);
    }, [onChange]);

    return (
        <TextFieldDecorator
            required={true}
            error={Boolean(error)}
            label="Enter password"
            helperText={error}
            variant="outlined"
            onChange={onChangeCallback}
            value={value}
            type={"password"}
        />
    )
})
