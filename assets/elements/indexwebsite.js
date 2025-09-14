import { h, render, Component } from "preact";
import register from 'preact-custom-element';
import Formwebsite from "./formwebsite";

class Website extends Component {
    constructor(props) {
        super(props);
        this.city=props.city;
    }
    static tagName = 'x-website';
    static observedAttributes = ['city'];

    render() {
        return  <Formwebsite city={this.city} />
    }
}

register(Website, 'x-website', ['city']);
