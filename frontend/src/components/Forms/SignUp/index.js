import * as React from 'react';
import {LoginContainer} from "./LoginInput/container";
import {PasswordContainer} from "./PasswordInput/container";
import './SignUp.css';
import {Button, CircularProgress} from "@material-ui/core";
import {useEffect, useMemo} from "react";
import {SuccessIcon} from "../../../ui/SuccessIcon/SuccessIcon";
import {ErrorIcon} from "../../../ui/ErrorIcon/ErrorIcon";
import {CheckboxContainer} from "./Checkbox/container";


export const SignUp = React.memo(props => {
    const {
        type,
        clearForm,
        onSignUpButtonClick,        
        onReturnButtonClick,
        isButtonDisabled
    } = props;

    useEffect(() => {
        if(type === "SUCCESS") {
            clearForm();
        }
    }, [type, clearForm]);

    const inputs = useMemo(() => {
        return (
            <>
                <div className={'signup__field'}>
                    <LoginContainer />
                </div>
                <div className={'signup__field'}>
                    <PasswordContainer />
                </div>
                <div className={'signup__field'}>
                    <CheckboxContainer />
                </div>
                <Button disabled={isButtonDisabled} onClick={onSignUpButtonClick} variant="contained" color="primary">
                    SignUp
                </Button>
            </>
        )
    }, [onSignUpButtonClick, isButtonDisabled]);

    const loader = useMemo(() => {
        return (
            <div className={'signup__loader-wrapper'}>
                <CircularProgress className={'signup__loader'} />
                {/*{type === SignUpFormType.LOGIN && (<p className={'signup__text'}>Try to login ...</p>)}*/}
                {/*{type === SignUpFormType.PATCH_KYC && (<p className={'signup__text'}>Try to patch kyc ...</p>)}*/}
            </div>
        )
    }, []);

    const success = (
            <>
                <div className={'signup__icon'}>
                    <SuccessIcon />
                </div>
                <p className={'signup__text'}>Success!</p>
            </>
        );

    const error = useMemo(() => {
        return (
            <>
                <div className={'signup__icon'}>
                    <ErrorIcon />
                </div>
                <p className={'signup__text'}>Oops!</p>
                <Button onClick={onReturnButtonClick} variant="contained" color="primary" >Try again</Button>
            </>
        )
    }, [onReturnButtonClick]);

    const content = useMemo(() => {
        if (type === "INPUT") {
            return inputs;
        }

        if (type === "ERROR") {
            return error;
        }

        return loader;
    }, [type, inputs, loader, error]);

    return (
        <div className={'signup'}>
            {content}
        </div>
    )
})

export default SignUp;