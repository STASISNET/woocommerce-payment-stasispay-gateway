import { composeWithDevTools } from 'redux-devtools-extension';
import { applyMiddleware, createStore } from "redux";
import thunk from 'redux-thunk'

import { combineReducers } from "redux";
import { signUpReducer } from "../components/Forms/SignUp/reducers";
import { loginReducer } from "../components/Forms/Login/reducers";

const rootReducer = combineReducers({
  signUp: signUpReducer,
  login: loginReducer
});


const composeEnhancers = composeWithDevTools({});

export default createStore(
  rootReducer,
  composeEnhancers(
    applyMiddleware(thunk)
  )
);
