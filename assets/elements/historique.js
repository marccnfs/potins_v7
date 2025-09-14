/** @jsx h */
import register from 'preact-custom-element';
import { h } from 'preact';


//import {Search,SearchInput} from "./search/Search.jsx";


//register(Search,'search-button',[])
//register(SearchInput,'search-input',[] )

const Greeting = ({ name = 'World' }) => (
    <p>Hello, {name}!</p>
);

register(Greeting, 'x-greeting', ['name']);


