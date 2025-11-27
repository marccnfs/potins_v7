import { Controller } from "@hotwired/stimulus";

/**
 * Orchestration du parcours "escape-team" côté joueurs.
 * Valide les étapes, consomme les indices et relaie les sous-étapes logiques.
 */
export default class extends Controller {
    static values = {
        runSlug: String,
        teamId: Number,
        completeUrl: String,
        hintUrl: String,
        steps: Object,
        stepStates: Object,
        expectedParts: { type: Number, default: 3 },
        currentStep: { type: Number, default: 1 },
        totalSteps: { type: Number, default: 5 },
        progressUrl: String,
        waitingUrl: String,
        pollInterval: { type: Number, default: 5000 },
    };

    static targets = ["feedback", "step", "runAlert"];

    initialize() {
        this.completedSteps = new Set();
        this.state = {};
    }

    connect() {
        this.state = this.stepStatesValue || {};
        this.currentStep = this.currentStepValue || 1;
        this.totalSteps = this.totalStepsValue || this.stepTargets.length || 5;
        this.completedSteps = new Set(
            Object.entries(this.state)
                .filter(([_, s]) => s && s.completedAt)
                .map(([idx]) => Number(idx))
        );

        this._markCompletedSteps();
        this._updateVisibleSteps();
        this._attachGlobalListeners();
        this._startStatusPolling();
    }

    disconnect() {
        document.removeEventListener("cryptex:solved", this._onCryptexSolved);
        if (this._pollTimer) {
            clearInterval(this._pollTimer);
        }
    }

    _attachGlobalListeners() {
        // Cryptex événements globaux
        this._onCryptexSolved = (event) => {
            const step = this._closestStep(event.target);
            if (!step) return;
            this.completeStep(step, { solution: event.detail?.solution || null });
        };
        document.addEventListener("cryptex:solved", this._onCryptexSolved);
    }

    _startStatusPolling() {
        if (!this.progressUrlValue || !this.waitingUrlValue || !this.teamIdValue) return;

        this._pollTimer = setInterval(() => this._refreshStatus(), this.pollIntervalValue);
        this._refreshStatus();
    }

    async _refreshStatus() {
        if (!this.progressUrlValue || this._redirecting) return;

        try {
            const response = await fetch(this.progressUrlValue, { headers: { "X-Requested-With": "XMLHttpRequest" } });
            const data = await response.json();

            const status = data?.status || "";
            const team = Array.isArray(data?.teams)
                ? data.teams.find((t) => t.teamId === this.teamIdValue)
                : null;

            if (status === "stopped" && team && !team.isCompleted) {
                this._showRunAlert("Le jeu est arrêté à la demande de l’admin, retour à l’attente.");
                this._redirecting = true;
                setTimeout(() => {
                    window.location.href = this.waitingUrlValue;
                }, 2000);
            }
        } catch (e) {
            // ignore network errors
        }
    }

    _showRunAlert(message) {
        if (!this.hasRunAlertTarget) return;

        this.runAlertTarget.textContent = message;
        this.runAlertTarget.hidden = false;
    }

    submitTextStep(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const step = Number(form.dataset.step || 0);
        if (!step || this.completedSteps.has(step)) return;

        const expected = (form.dataset.solution || "").trim().toLowerCase();
        const value = (form.querySelector("input, textarea")?.value || "").trim().toLowerCase();

        if (!expected) return;

        if (expected === value) {
            this._setFeedback(step, "✅ Réponse validée !", true);
            this.completeStep(step, { answer: value });
        } else {
            this._setFeedback(step, "Mot ou phrase incorrect·e, réessaie en vérifiant l’orthographe.", false);
        }
    }

    recordLogicPart(event) {
        const wrapper = event.currentTarget;
        const step = Number(wrapper.dataset.step || 0);
        const partKey = wrapper.dataset.partKey || null;
        if (!step || !partKey || this.completedSteps.has(step)) return;

        this.completeStep(step, { part: partKey }, partKey);
        this._setFeedback(step, `Sous-épreuve ${partKey} validée ✔️`, true);
    }

    consumeHint(event) {
        const step = Number(event.currentTarget.dataset.step || 0);
        if (!step) return;

        const url = this.hintUrlValue;
        if (!url) return;

        fetch(url, {
            method: "POST",
            headers: { "X-Requested-With": "XMLHttpRequest" },
            body: new URLSearchParams({ count: 1, step }),
        }).catch(() => {});
    }

    async completeStep(step, metadata = {}, partialKey = null) {
        if (!this.completeUrlValue) return;
        const url = this.completeUrlValue.replace("__STEP__", step);
        const params = new URLSearchParams();

        if (partialKey) {
            params.set("partialKey", partialKey);
            params.set("expectedParts", this.expectedPartsValue?.toString() || "3");
        }

        Object.entries(metadata || {}).forEach(([key, value]) => {
            if (value === undefined) return;
            params.set(`meta[${key}]`, value);
        });

        try {
            const response = await fetch(url, {
                method: "POST",
                headers: { "X-Requested-With": "XMLHttpRequest" },
                body: params,
            });

            const data = await response.json();

            if (!response.ok) {
                const message = data?.error || "Request failed";
                const error = new Error(message);
                error.warning = data?.warning || false;
                throw error;
            }

            if (data?.stepStates) {
                this.state = data.stepStates;
            }
            const stepState = this.state?.[step] || {};
            const isStepCompleted = Boolean(stepState.completedAt) ||
                (typeof data?.currentStep === "number" && data.currentStep > step) ||
                Boolean(data?.completed);

            if (isStepCompleted) {
                this.completedSteps.add(step);
            } else {
                this.completedSteps.delete(step);
            }

            if (typeof data?.currentStep === "number") {
                this.currentStep = data.currentStep || this.totalSteps;
            }
            this._markCompletedSteps();
            this._updateVisibleSteps();
        } catch (e) {
            const message = e?.message || "Impossible d’enregistrer l’étape. Vérifie que le jeu est lancé.";
            this._setFeedback(step, message, false);
            if (e?.warning) {
                this._flagWarning(step);
            }
        }
    }

    _setFeedback(step, message, isSuccess) {
        const target = this.feedbackTargets.find((t) => Number(t.dataset.step) === step);
        if (!target) return;
        target.textContent = message;
        target.className = isSuccess ? "feedback ok" : "feedback ko";
    }
    _flagWarning(step) {
        const target = this.stepTargets.find((t) => Number(t.dataset.step) === step);
        if (!target) return;
        target.classList.add("has-warning");
    }

    _markCompletedSteps() {
        this.stepTargets.forEach((el) => {
            const step = Number(el.dataset.step || 0);
            if (!step || !this.completedSteps.has(step)) return;

            el.classList.add("is-complete");
            el.querySelectorAll("input, textarea, button").forEach((n) => {
                n.disabled = true;
            });
        });
    }

    _updateVisibleSteps() {
        this.stepTargets.forEach((el) => {
            const step = Number(el.dataset.step || 0);
            if (!step) return;

            const shouldShow = this.completedSteps.has(step) || step <= this.currentStep;
            el.hidden = !shouldShow;
        });
    }

    _closestStep(el) {
        const node = el?.closest?.("[data-step]");
        return node ? Number(node.dataset.step || 0) : null;
    }
}
