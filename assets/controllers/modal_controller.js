// assets/controllers/modal_controller.js
import { Controller } from "@hotwired/stimulus";
export default class extends Controller {
    static targets = ["root"]
    open(){ this.rootTarget.hidden = false; }
    close(){ this.rootTarget.hidden = true; }
}
