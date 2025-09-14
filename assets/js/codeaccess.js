import { h, render, Component } from "preact";
import register from 'preact-custom-element';
import {Testaccesform} from "../formulaires/testaccessform";

class TestAccess extends Component {
    constructor(props) {
        super(props);
    }
    render() {
        return  <Testaccesform />
    }
}

register(TestAccess, 'test-access');






