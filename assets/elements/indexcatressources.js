import { h, render, Component } from "preact";
import register from 'preact-custom-element';
import FormRessources from "./formressources";

class Ressources extends Component {
    constructor(props) {
        super(props);
        this.caty=props.cat;
    }
    static tagName = 'x-ressources';
    static observedAttributes = ['cat'];

    render() {
        return  <FormRessources cat={this.cat} />
    }
}

register(Ressources, 'x-ressources', ['cat']);
