import { Button, CircularProgress } from '@material-ui/core';
import * as React from 'react';
import { useMemo } from 'react';
import { SuccessIcon } from '../../../ui/SuccessIcon/SuccessIcon';
import './Login.css';
import { LoginContainer } from './LoginInput/container';
import { PasswordContainer } from './PasswordInput/container';

export const Login = React.memo((props) => {
    const {
        type,
        onLoginButtonClick,
        token,
        isButtonDisabled,
        upgrade,
    } = props;

    const inputs = useMemo(() => {
        return (
            <>
                <div className={'login__field'}>
                    <LoginContainer />
                </div>
                {upgrade ? (
                    <p className={'stss-pay-details-message'}>
                        Please complete your Full verification at&nbsp;
                        <a
                            href={'https://stasis.net/sellback/'}
                            target={'_blank'}
                        >
                            STASIS Sellback
                        </a>
                        &nbsp;to proceed. It will take about 10 minutes.
                    </p>
                ) : null}
                <div className={'login__field'}>
                    <PasswordContainer />
                </div>
                <Button
                    onClick={onLoginButtonClick}
                    disabled={isButtonDisabled}
                    variant="contained"
                    color="primary"
                >
                    Login
                </Button>
            </>
        );
    }, [onLoginButtonClick, isButtonDisabled, upgrade]);

    const loader = useMemo(() => {
        return (
            <div className={'login__loader'}>
                <CircularProgress />
            </div>
        );
    }, []);

    const success = useMemo(() => {
        return (
            <div className={'login__confirmed'}>
                <div className={'login__icon'}>
                    <SuccessIcon />
                </div>
                <p className={'login__text'}>Success!</p>
            </div>
        );
    }, []);

    const content = useMemo(() => {
        if (token) {
            return success;
        }

        if (type === 'INPUT') {
            return inputs;
        }

        return loader;
    }, [type, inputs, loader, success, token]);

    return <div className={'login'}>{content}</div>;
});

export default Login;
