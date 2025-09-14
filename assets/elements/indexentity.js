import { h, render, Component } from "preact";
import register from 'preact-custom-element';
import {Testentityform} from "../formulaires/testentityform";
import FormAdBoard from "./formaddboard";

class EntityForAdd extends Component {
    constructor(props) {
        super(props);
        this.author=props.author;
    }
    static tagName = 'add-entity';
    static observedAttributes = ['author'];
    render() {
        return  <FormAdBoard author={this.author} />
    }
}

register(EntityForAdd, 'add-entity',['author']);