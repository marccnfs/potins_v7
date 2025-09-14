import { h, render, Component } from "preact";
import register from 'preact-custom-element';
import FormOffrepotin from "./formoffrepotin";


class OffrePotin extends Component {
    constructor(props) {
        super(props);
        if(props.event !==""){
            this.offre=JSON.parse(props.offre);
        }else{
            this.offre= false;
        }
        this.board=props.board;

    }

    render() {
            return  <FormOffrepotin offre={this.offre} board={this.board}/>
    }
}

register(OffrePotin, 'x-offrepotin',  ['offre','board']);
