import * as React from 'react';
import {TextField} from "@material-ui/core";
import {makeStyles} from "@material-ui/core/styles";
import classNames from "classnames";

const useStyles = makeStyles({
    root: {
        width: '100%',
        maxWidth: '430px'
    },
});

export const TextFieldDecorator = React.memo(props => {
    const classes = useStyles();

   return (
       <TextField
           className={classNames(props.className, classes.root)}
           size={'small'}
           {...props}
       />
   )
});
