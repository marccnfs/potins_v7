// assets/controllers/hud_controller.js
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = { total: Number, step: Number, completedSteps: Array, slug: String }
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

    completedStepsValueChanged(value){
        this._completedSteps = new Set(Array.isArray(value) ? value : []);
        this.refreshCompletionState();
    }

    refreshCompletionState(){
        if (!this.hasProgressPanelTarget) return;

        const dots = this.progressPanelTarget.querySelectorAll(".step-dot[data-step]");
        const completed = this._completedSteps ?? new Set();
        const current = this.hasStepValue ? this.stepValue : null;
        dots.forEach((dot) => {
            const step = Number.parseInt(dot.dataset.step ?? "", 10);
            if (Number.isNaN(step)) {
                return;
            }
            const isComplete = completed.has(step);
            dot.classList.toggle("is-complete", isComplete);

            if (current !== null) {
                const isCurrent = step === current;
                dot.classList.toggle("is-current", isCurrent);
                if (!isComplete && !isCurrent && step > current) {
                    dot.dataset.status = "upcoming";
                } else if (isComplete) {
                    dot.dataset.status = "complete";
                } else if (isCurrent) {
                    dot.dataset.status = "current";
                } else {
                    dot.removeAttribute("data-status");
                }
            } else if (isComplete) {
                dot.dataset.status = "complete";
            } else {
                dot.removeAttribute("data-status");
            }
        });
    }

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


    const shouldAutoAdvance = this.hasSlugValue && this.hasStepValue && this.hasTotalValue && (!this.hasNextBtnTarget || this.nextBtnTarget.hidden === true);
    if (shouldAutoAdvance) {
        const nextStep = this.stepValue + 1;
        const targetUrl = nextStep <= this.totalValue
            ? `/play/${this.slugValue}/step/${nextStep}`
            : `/play/${this.slugValue}/the-end`;

        const currentDot = this.progressPanelTarget?.querySelector(`.step-dot[data-step="${this.stepValue}"]`);
        if (currentDot) {
            currentDot.classList.remove("is-current");
            if (currentDot.classList.contains("is-complete")) {
                currentDot.dataset.status = "complete";
            } else {
                currentDot.removeAttribute("data-status");
            }
        }
        const nextDot = this.progressPanelTarget?.querySelector(`.step-dot[data-step="${nextStep}"]`);
        if (nextDot) {
            nextDot.classList.add("is-current");
            nextDot.dataset.status = "current";
        }

        window.setTimeout(() => { window.location.assign(targetUrl); }, 2200);
    }


    }
}
