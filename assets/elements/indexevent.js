import { h, render, Component } from "preact";
import register from 'preact-custom-element';
import Formevent from "./formevent";


class Event extends Component {
    constructor(props) {
        super(props);
        if(props.event !==""){
            this.event=JSON.parse(props.event);
        }else{
            this.event= false;
        }
        if(props.partners !==""){
            this.partners=JSON.parse(props.partners);
        }else{
            this.partners= false;
        }
        this.city=props.city;
        this.board=props.board;
    }

    render() {
            return  <Formevent event={this.event} partners={this.partners} board={this.board} city={this.city}/>
    }
}

register(Event, 'x-event',  ['board','potin','partners','event','city']);
