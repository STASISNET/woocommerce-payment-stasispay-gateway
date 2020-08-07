import * as React from 'react';
import { red } from '@material-ui/core/colors';
import {makeStyles} from "@material-ui/core/styles";
import CloseIcon from '@material-ui/icons/Close';

const useStyles = makeStyles((theme) => ({
    circle: {
        backgroundColor: red[500],
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        width: '50px',
        height: '50px',
        borderRadius: '40px'
    }
}));

export const ErrorIcon = React.memo(props => {
    const classes = useStyles();

    return (
        <div className={classes.circle}>
            <CloseIcon htmlColor={'white'}/>
        </div>
    )
});
