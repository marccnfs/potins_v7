import { h, render, Component } from "preact";
import register from 'preact-custom-element';
import Formewmediaboard from "./formewmediaboard";

class NewMediaBoard extends Component {
    constructor(props) {
        super(props);
        this.customer=props.customer;
    }

    render() {
            return  <Formewmediaboard customer={this.customer}/>
    }
}

register(NewMediaBoard, 'x-mediaboard',  ['customer']);
