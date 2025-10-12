import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["aside", "toggle", "toggleLabel"];
    static classes = ["collapsed"];

    connect() {
        this.ensureInitialState();
    }

    toggle(event) {
        event.preventDefault();
        const isCollapsed = this.element.classList.toggle(this.collapsedClass);
        this.updateState(isCollapsed);
    }

    ensureInitialState() {
        const isCollapsed = this.element.classList.contains(this.collapsedClass);
        if (!isCollapsed) {
            this.element.classList.add(this.collapsedClass);
        }
        this.updateState(true);
    }

    updateState(collapsed) {
        if (this.hasToggleTarget) {
            this.toggleTarget.setAttribute("aria-expanded", String(!collapsed));
            this.toggleTarget.setAttribute("aria-label", collapsed ? "Afficher les filtres" : "Masquer les filtres");
        }
        if (this.hasToggleLabelTarget) {
            this.toggleLabelTarget.textContent = collapsed ? "Afficher les filtres" : "Masquer les filtres";
        }
        if (this.hasAsideTarget) {
            this.asideTarget.setAttribute("aria-hidden", String(collapsed));
        }
    }
}
