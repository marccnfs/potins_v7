import { h, render, Component } from "preact";
import register from 'preact-custom-element';
import Formeventpotin from "./formeventpotin";

class EventPotin extends Component {
    constructor(props) {
        super(props);
        if (props.event !== "") {
            this.event = JSON.parse(props.event);
        } else {
            this.event = false;
        }
        if (props.mediatheques && props.mediatheques !== "") {
            try {
                this.mediatheques = JSON.parse(props.mediatheques);
            } catch (e) {
                this.mediatheques = [];
            }
        } else {
            this.mediatheques = [];
        }
        this.board = props.board;
        this.potin = props.potin;
    }

    render() {
        return  <Formeventpotin event={this.event} potin={this.potin} board={this.board} mediatheques={this.mediatheques}/>
    }
}

register(EventPotin, 'x-eventpotin',  ['event','potin','board','mediatheques']);
