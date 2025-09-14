// assets/controllers/playflow_controller.js
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["next"]
    connect(){
        this.onSolved = ()=> this.showNext();
        document.addEventListener("puzzle:solved", this.onSolved, { once: true });
    }
    disconnect(){ document.removeEventListener("puzzle:solved", this.onSolved); }
    showNext(){ if (this.hasNextTarget) this.nextTarget.hidden = false; }
}
