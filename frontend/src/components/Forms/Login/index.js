import * as React from 'react';
import {useMemo} from "react";
import {LoginContainer} from "./LoginInput/container";
import {PasswordContainer} from "./PasswordInput/container";
import {Button, CircularProgress} from "@material-ui/core";
import './Login.css';
import {SuccessIcon} from "../../../ui/SuccessIcon/SuccessIcon";


export const Login = React.memo(props => {
   const { type, onLoginButtonClick, token, isButtonDisabled } = props;

   const inputs = useMemo(() => {
       return (
           <>
               <div className={'login__field'}>
                   <LoginContainer />
               </div>
               <div className={'login__field'}>
                    <PasswordContainer />
               </div>
               <Button onClick={onLoginButtonClick} disabled={isButtonDisabled} variant="contained" color="primary">
                   Login
               </Button>
           </>
       )
   }, [onLoginButtonClick, isButtonDisabled]);

   const loader = useMemo(() => {
       return (
           <div className={'login__loader'}>
               <CircularProgress />
           </div>
       )
   }, []);

   const success = useMemo(() => {
       return (
           <div className={'login__confirmed'}>
               <div className={'login__icon'}>
                   <SuccessIcon />
               </div>
               <p className={'login__text'}>Success!</p>
           </div>
       )
   }, []);

   const content = useMemo(() => {
       if (token) {
           return success;
       }

       if (type === "INPUT") {
           return inputs;
       }

       return loader;

   }, [type, inputs, loader, success, token]);

   return (
       <div className={'login'}>
           {content}
       </div>
   );
});

export default Login;
