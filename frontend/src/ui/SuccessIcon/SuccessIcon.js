import * as React from 'react';
import { green } from '@material-ui/core/colors';
import {makeStyles} from "@material-ui/core/styles";
import CheckIcon from '@material-ui/icons/Check';

const useStyles = makeStyles((theme) => ({
    circle: {
        backgroundColor: green[500],
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        width: '50px',
        height: '50px',
        borderRadius: '40px'
    }
}));

export const SuccessIcon = React.memo(props => {
    const classes = useStyles();

    return (
        <div className={classes.circle}>
            <CheckIcon htmlColor={'white'}/>
        </div>
    )
});
