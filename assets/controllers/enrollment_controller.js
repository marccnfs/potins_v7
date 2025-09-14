import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["enrollForm","cancelForm"]

    async submit(e) {
        // progressive: commente la ligne suivante pour garder navigation full-page
        e.preventDefault();

        const form = e.currentTarget.closest('form');
        if (!form) return;

        const res = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        // Si contrôleurs renvoient une redirection normale, on rafraîchit
        if (!res.ok) { window.location.reload(); return; }

        // Simplicité: on recharge pour refléter l’état
        window.location.reload();
    }
}
