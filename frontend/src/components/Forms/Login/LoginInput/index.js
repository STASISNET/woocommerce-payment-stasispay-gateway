import * as React from 'react';
import { useCallback } from "react";
import { TextFieldDecorator } from "../../../../ui/TextFieldDecorator/TextFieldDecorator";


export const LoginInput = React.memo(props => {
    const { value, error, onChange } = props;

    const onChangeCallback = useCallback((event) => {
        onChange(event.target.value);
    }, [onChange]);

    return (
        <TextFieldDecorator
            required={true}
            error={Boolean(error)}
            label="Your email"
            helperText={error}
            variant="outlined"
            onChange={onChangeCallback}
            value={value}
        />
    )
})
