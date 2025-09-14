import { h, render, Component } from "preact";
import register from 'preact-custom-element';
import Formpartner from "./formpartner";


class Partner extends Component {
    constructor(props) {
        super(props);
        if(props.board !==""){
            this.board=props.board;
        }else{
            this.board= false;
        }
    }

    static tagName = 'x-partner';
    // Track these attributes:
    static observedAttributes = ['board'];

    render() {
        return  <Formpartner board={this.board} />
    }
}

register(Partner, 'x-partner', ['board']);
