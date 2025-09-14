import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["category"]
    connect() {
        if (this.hasCategoryTarget) {
            this.categoryTarget.addEventListener('change', () => {
                document.dispatchEvent(new CustomEvent('agenda-filters:changed'));
            });
        }
    }
}
