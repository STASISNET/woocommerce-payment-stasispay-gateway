import * as React from 'react';
import {useMemo, useState} from "react";
import './Forms.css';
import {SignUpContainer} from "./SignUp/container";
import {LoginContainer} from "./Login/container";


const SIGNUP = 'SIGNUP'
const LOGIN = 'LOGIN'

const Forms = React.memo(props => {
    const [regType, setRegType] = useState(LOGIN);

    const header = useMemo(() => {
        return (
            <div className={'form__header'}>
                {[LOGIN, SIGNUP].map(type => {
                    if (regType === type) {
                        return (
                            <div key="div1" className={'form__header-item form__header-item_active'}>{type}</div>
                        );
                    }

                    return (
                        <div key='div2' className={'form__header-item'} onClick={() => setRegType(type)}>{type}</div>
                    )
                })}
            </div>
        )
    }, [regType]);

    const content = useMemo(() => {
        if(regType === SIGNUP) {
            return <SignUpContainer />
        }
        if(regType === LOGIN) {
            return <LoginContainer />;
        }
        return null;
    }, [regType])

   return (
       <div className={'form'}>
           {header}
           <div className={'form__content'}>
               {content}
           </div>
       </div>
   );
});

export default Forms;