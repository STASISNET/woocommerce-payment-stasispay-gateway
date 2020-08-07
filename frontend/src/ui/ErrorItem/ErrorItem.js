import * as React from 'react';
import './ErrorItem.css';

export const ErrorItem = React.memo(props => (<div className={'error-item'}>{props.children}</div>));
