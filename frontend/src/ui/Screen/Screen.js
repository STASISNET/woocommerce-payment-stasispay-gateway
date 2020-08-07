import * as React from 'react';
import classNames from 'classnames';
import './Screen.css';

export const Screen = React.memo(({isCenter, isGrey, style, children}) => {
    return (
        <div
            className={classNames('screen', isCenter ? 'screen_center' : undefined, isGrey ? 'screen_grey' : undefined)}
            style={style && {...style}}
        >
            <div className={'screen__inner'}>
                {children}
            </div>
        </div>
    )
})
