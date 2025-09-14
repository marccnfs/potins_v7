import { h, render, Component } from "preact";
import register from 'preact-custom-element';
import {Addcommentwp} from "../formulaires/addcommentwp";
import {Testmailmsg} from "../formulaires/testmailmsg";

class TestMailForMsg extends Component {
    static observedAttributes = ['slug'];
    render({slug,id,module}) {
        return  <Testmailmsg slug={slug} id={id} module={module}/>
    }
}

class AddComment extends Component {
    static observedAttributes = ['slug','idslug','id','module'];
    render({msgid,slug,id,module}) {
        return  <Addcommentwp msgid={msgid} slug={slug} id={id} module={module}/>
    }
}

register(AddComment, 'add-comment');
register(TestMailForMsg, 'test-mailmsg');

/*
$('textarea').each(function () {
    this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;');
}).on('input', function () {
    console.log("input texte")
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
    //scrl.removeClass('scrl_hdn')
});

 */