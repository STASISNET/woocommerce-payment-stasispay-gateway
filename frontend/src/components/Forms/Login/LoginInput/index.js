import * as React from 'react';
import {TextFieldDecorator} from "../../../../ui/TextFieldDecorator/TextFieldDecorator";
import {useCallback} from "react";


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
