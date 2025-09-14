// assets/controllers/telemetry_controller.js
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = { slug: String, csrf: String }

    connect(){
        // Start dès l’entrée de jeu
        fetch(`/play/${this.slugValue}/start`, {
            method: 'POST',
            headers: {'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfValue}
        });
        // Écoute des hints
        document.addEventListener('puzzle:hint', () => {
            fetch(`/play/${this.slugValue}/hint`, {
                method: 'POST',
                headers: {'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfValue}
            });
        });
        // Fin (on attend d’avoir une durée côté front)
        document.addEventListener('puzzle:gameover', (e) => {
            const ms = e.detail?.durationMs ?? 0;
            const body = new FormData();
            body.append('durationMs', String(ms));
            fetch(`/play/${this.slugValue}/finish`, {
                method: 'POST',
                headers: {'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfValue},
                body
            }).then(r=>r.json()).then(({score})=>{
                // tu peux afficher un toast score ici si tu veux
            });
        });
    }
}
