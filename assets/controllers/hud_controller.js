// assets/controllers/hud_controller.js
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = { total: Number, step: Number, completedSteps: Array, slug: String }
    static targets = ["progressPanel","helpPanel","nextBtn","progressBar","progressCaption"]

    connect(){
        this.onSolved = this.handleSolved.bind(this);
        document.addEventListener("puzzle:solved", this.onSolved, { once: true });
        this._completedSteps = this.buildCompletedSet(this.hasCompletedStepsValue ? this.completedStepsValue : []);
        this.refreshCompletionState();
        this.updateNavigationState();
        // Option : toast d’accueil
        const toast = document.querySelector(".toast-stack")?.controller;
    }

    disconnect(){
        document.removeEventListener("puzzle:solved", this.onSolved);
    }

    toggleHelp(){ this.helpPanelTarget?.toggleAttribute("hidden"); }
    toggleProgress(){ this.progressPanelTarget?.toggleAttribute("hidden"); }

    completedStepsValueChanged(value){
        this._completedSteps = this.buildCompletedSet(value);
        this.refreshCompletionState();
        this.updateNavigationState();
    }

    stepValueChanged(){
        this.refreshCompletionState();
        this.updateNavigationState();
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
            const isCurrent = current !== null && step === current;
            const isComplete = completed.has(step);
            const shouldLock = !isComplete && !isCurrent && (current === null || step > current);

            dot.classList.toggle("is-current", isCurrent);
            dot.classList.toggle("is-complete", isComplete);
            dot.classList.toggle("is-locked", shouldLock);

            if (shouldLock) {
                dot.setAttribute("aria-disabled", "true");
            } else {
                dot.removeAttribute("aria-disabled");
            }
        });
        this.refreshProgressHeader();
    }

    handleSolved(){
        // Affiche bouton “suivant”
        if (this.hasNextBtnTarget) this.nextBtnTarget.hidden = false;

        if (this.hasStepValue) {
            this._completedSteps ??= new Set(this.hasCompletedStepsValue ? this.completedStepsValue : []);
            this._completedSteps.add(this.stepValue);
            this.completedStepsValue = Array.from(this._completedSteps);
            this.refreshCompletionState();
            this.updateNavigationState();
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
                currentDot.classList.remove("is-locked");
                currentDot.classList.add("is-complete");
                currentDot.removeAttribute("aria-disabled");
            }
            const nextDot = this.progressPanelTarget?.querySelector(`.step-dot[data-step="${nextStep}"]`);
            if (nextDot) {
                nextDot.classList.remove("is-locked");
                nextDot.classList.add("is-current");
                nextDot.removeAttribute("aria-disabled");
            }
            window.setTimeout(() => { window.location.assign(targetUrl); }, 2200);
        }


    }
    updateNavigationState(){
        if (!this.hasNextBtnTarget) return;
        const currentStep = this.hasStepValue ? this.stepValue : null;
        if (currentStep === null) {
            this.nextBtnTarget.hidden = true;
            return;
        }
        const completed = this._completedSteps ?? new Set();
        const isCompleted = completed.has(currentStep);
        this.nextBtnTarget.hidden = !isCompleted;
    }

    refreshProgressHeader(){
        const current = this.hasStepValue ? this.stepValue : null;
        const total = this.hasTotalValue ? this.totalValue : null;
        if (current === null || total === null || total <= 0) return;

        const ratio = Math.min(Math.max(current / total, 0), 1);
        if (this.hasProgressBarTarget) {
            this.progressBarTarget.style.width = `${Math.round(ratio * 100)}%`;
        }
        if (this.hasProgressCaptionTarget) {
            this.progressCaptionTarget.textContent = `Étape ${current} sur ${total}`;
        }
    }

    buildCompletedSet(value){
        if (!Array.isArray(value)) {
            return new Set();
        }
        const normalized = value
            .map((step) => {
                if (typeof step === "number" && Number.isInteger(step)) {
                    return step;
                }
                if (typeof step === "string" && step.trim() !== "" && /^\d+$/.test(step.trim())) {
                    return Number.parseInt(step, 10);
                }
                return null;
            })
            .filter((step) => typeof step === "number");
        return new Set(normalized);
    }
}
