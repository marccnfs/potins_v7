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
        this._onHint = () => {
            fetch(`/play/${this.slugValue}/hint`, {
                method: 'POST',
                headers: {'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfValue}
            });
        };
        document.addEventListener('puzzle:hint', this._onHint);

        // Fin (on attend d’avoir une durée côté front)
        this._onGameover = (e) => {
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
        };
        document.addEventListener('puzzle:gameover', this._onGameover);
    }
    disconnect(){
        document.removeEventListener('puzzle:hint', this._onHint);
        document.removeEventListener('puzzle:gameover', this._onGameover);
    }
}
