import { h, render, Component } from "preact";
import register from 'preact-custom-element';
import FormAdress from "./formadress";



class Adress extends Component {
    static observedAttributes = ['id','module'];
    render({id,module}) {
        return  <FormAdress  id={id} module={module}/>
    }
}

register(Adress, 'x-adress');
