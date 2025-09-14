import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["start","end","timezone"]

    connect() {
        // Pré-remplir timezone si vide
        if (this.hasTimezoneTarget && (!this.timezoneTarget.value || this.timezoneTarget.value.trim()==='')) {
            try { this.timezoneTarget.value = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Europe/Paris'; }
            catch { this.timezoneTarget.value = 'Europe/Paris'; }
        }

        // Vérif on-change
        if (this.hasStartTarget) this.startTarget.addEventListener('change', () => this.validateRange());
        if (this.hasEndTarget)   this.endTarget.addEventListener('change', () => this.validateRange());
    }

    validateRange() {
        if (!this.hasStartTarget || !this.hasEndTarget) return;
        const s = new Date(this.startTarget.value);
        const e = new Date(this.endTarget.value);
        if (isFinite(s) && isFinite(e) && e < s) {
            this.endTarget.setCustomValidity("La fin doit être après le début.");
        } else {
            this.endTarget.setCustomValidity("");
        }
    }
}
