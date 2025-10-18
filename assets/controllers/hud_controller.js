// assets/controllers/hud_controller.js
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = { total: Number, step: Number, completedSteps: Array }
    static targets = ["progressPanel","helpPanel","nextBtn"]

    connect(){
        this.onSolved = this.handleSolved.bind(this);
        document.addEventListener("puzzle:solved", this.onSolved, { once: true });
        this._completedSteps = new Set(this.hasCompletedStepsValue ? this.completedStepsValue : []);
        this.refreshCompletionState();
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

        if (this.hasStepValue) {
            this._completedSteps ??= new Set(this.hasCompletedStepsValue ? this.completedStepsValue : []);
            this._completedSteps.add(this.stepValue);
            this.completedStepsValue = Array.from(this._completedSteps);
            this.refreshCompletionState();
        }

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


    completedStepsValueChanged(value){
        this._completedSteps = new Set(Array.isArray(value) ? value : []);
        this.refreshCompletionState();
    }

    refreshCompletionState(){
        if (!this.hasProgressPanelTarget) return;

        const dots = this.progressPanelTarget.querySelectorAll(".step-dot[data-step]");
        const completed = this._completedSteps ?? new Set();
        dots.forEach((dot) => {
            const step = Number.parseInt(dot.dataset.step ?? "", 10);
            if (Number.isNaN(step)) {
                return;
            }
            if (completed.has(step)) {
                dot.classList.add("is-complete");
                dot.dataset.status = "complete";
            } else {
                dot.classList.remove("is-complete");
                dot.removeAttribute("data-status");
            }
        });
    }
}
