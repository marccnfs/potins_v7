// assets/controllers/toast_controller.js
import { Controller } from "@hotwired/stimulus";
export default class extends Controller {
    connect(){ this.element.controller = this; } // accès simple depuis HUD
    push(text){
        const el = document.createElement("div");
        el.className = "toast"; el.textContent = text;
        this.element.appendChild(el);
        setTimeout(()=> el.remove(), 3000);
    }
}
