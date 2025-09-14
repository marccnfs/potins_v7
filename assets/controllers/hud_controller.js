// assets/controllers/hud_controller.js
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = { total: Number, step: Number }
    static targets = ["progressPanel","helpPanel","nextBtn"]

    connect(){
        this.onSolved = this.handleSolved.bind(this);
        document.addEventListener("puzzle:solved", this.onSolved, { once: true });

        // Option : toast d’accueil
        const toast = document.querySelector(".toast-stack")?.controller;
    }

    disconnect(){
        document.removeEventListener("puzzle:solved", this.onSolved);
    }

    toggleHelp(){ this.helpPanelTarget?.toggleAttribute("hidden"); }
    toggleProgress(){ this.progressPanelTarget?.toggleAttribute("hidden"); }

    handleSolved(){
        // Affiche bouton “suivant”
        if (this.hasNextBtnTarget) this.nextBtnTarget.hidden = false;

        // Petit toast
        const stack = document.querySelector(".toast-stack");
        if (stack) {
            const t = document.createElement("div");
            t.className = "toast"; t.textContent = "✅ Bien joué ! Clique sur « Étape suivante »";
            stack.appendChild(t);
            setTimeout(()=> t.remove(), 3000);
        }

        // Ouvre une modale d’info si tu veux (ex: document.querySelector('[data-controller="modal"]')?.controller.open())
    }
}
