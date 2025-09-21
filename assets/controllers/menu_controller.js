// assets/controllers/menu_controller.js
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["overlay", "panel"];

    connect() {
        this.handleKeydown = this.handleKeydown.bind(this);
    }

    disconnect() {
        document.removeEventListener("keydown", this.handleKeydown);
    }

    open() {
        if (this.hasOverlayTarget) {
            this.overlayTarget.classList.remove("hidden");
        }

        if (this.hasPanelTarget) {
            this.panelTarget.classList.remove("hidden");
        }

        document.addEventListener("keydown", this.handleKeydown);
    }

    close() {
        if (this.hasOverlayTarget) {
            this.overlayTarget.classList.add("hidden");
        }

        if (this.hasPanelTarget) {
            this.panelTarget.classList.add("hidden");
        }

        document.removeEventListener("keydown", this.handleKeydown);
    }

    handleKeydown(event) {
        if (event.key === "Escape") {
            this.close();
        }
    }
}
