import { Controller } from "@hotwired/stimulus";

const STORAGE_PREFIX = "eg:";

export default class extends Controller {
    static targets = [
        "pool",
        "slots",
        "input",
        "feedback",
        "reveal",
        "revealText",
        "finalTime",
        "celebration",
        "duration",
        "module",
        "prompt"
    ];

    static values = {
        fragments: Array,
        finalPrompt: String,
        finalReveal: String,
        slug: String,
        missingFragments: Array,
        totalSteps: Number
    };

    connect() {
        this.fragmentMap = new Map();
        for (const fragment of this.fragmentsValue || []) {
            if (!fragment || typeof fragment.step !== "number") continue;
            this.fragmentMap.set(fragment.step, {
                text: typeof fragment.text === "string" ? fragment.text : "",
                label: typeof fragment.label === "string" ? fragment.label : `Étape ${fragment.step}`
            });
        }

        this.expectedOrder = Array.from({ length: this.totalStepsValue }, (_, idx) => idx + 1);
        this.expectedPhrase = this.expectedOrder
            .map(step => (this.fragmentMap.get(step)?.text || "").trim())
            .join(" ")
            .replace(/\s+/g, " ")
            .trim();
        this.expectedNormalized = this.normalize(this.expectedPhrase);
        this.storageBase = `${STORAGE_PREFIX}${this.slugValue}`;
        this.orderKey = `${this.storageBase}:finalOrder`;
        this.durationKey = `${this.storageBase}:totalMs`;
        this.startedKey = `${this.storageBase}:startedAt`;
        this.completed = false;
        this.state = {
            pool: [],
            slots: new Array(this.totalStepsValue).fill(null)
        };

        if (Array.isArray(this.missingFragmentsValue) && this.missingFragmentsValue.length > 0) {
            this.moduleTarget?.classList.add("has-missing");
        }

        this.buildSlots();
        this.restoreProgress();
        this.updateDuration();
        if (this.hasInputTarget) {
            this._onKeydown = (event) => {
                if (event.key === "Enter" && (event.metaKey || event.ctrlKey)) {
                    event.preventDefault();
                    this.validate();
                }
            };
            this.inputTarget.addEventListener("keydown", this._onKeydown);
        }
    }

    disconnect() {
        if (this._onKeydown && this.hasInputTarget) {
            this.inputTarget.removeEventListener("keydown", this._onKeydown);
        }
    }

    buildSlots() {
        this.slotElements = [];
        this.slotsTarget.innerHTML = "";
        this.expectedOrder.forEach((step, index) => {
            const li = document.createElement("li");
            li.className = "finale-slot";
            li.dataset.index = String(index);
            li.innerHTML = `
                <span class="finale-slot__index">${index + 1}</span>
                <span class="finale-slot__body">
                    <span class="finale-slot__text">…</span>
                    <span class="finale-slot__label">${this.escapeHtml(this.fragmentMap.get(step)?.label ?? ``)}</span>
                </span>`;
            li.addEventListener("click", () => this.clearSlot(index));
            li.addEventListener("dragover", (event) => {
                event.preventDefault();
                li.classList.add("is-droppable");
            });
            li.addEventListener("dragleave", () => li.classList.remove("is-droppable"));
            li.addEventListener("drop", (event) => {
                event.preventDefault();
                li.classList.remove("is-droppable");
                const raw = event.dataTransfer?.getData("text/plain");
                const stepFromDrag = raw ? Number(raw) : NaN;
                if (!Number.isNaN(stepFromDrag)) {
                    this.placeStepInSlot(stepFromDrag, index);
                }
            });
            this.slotsTarget.append(li);
            this.slotElements.push(li);
        });
    }

    shuffle() {
        const steps = [...this.expectedOrder];
        for (let i = steps.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [steps[i], steps[j]] = [steps[j], steps[i]];
        }
        this.state.pool = steps;
        this.state.slots = new Array(this.totalStepsValue).fill(null);
        this.renderPool();
        this.renderSlots();
        this.showFeedback("");
        if (this.hasInputTarget) {
            this.inputTarget.value = "";
        }
        this.completed = false;
        this.moduleTarget?.classList.remove("is-complete");
        if (this.hasRevealTarget) {
            this.revealTarget.hidden = true;
        }
    }

    renderPool() {
        if (!this.hasPoolTarget) return;
        this.poolTarget.innerHTML = "";
        this.state.pool.forEach((step) => {
            const fragment = this.fragmentMap.get(step);
            const button = document.createElement("button");
            button.type = "button";
            button.className = "finale-fragment";
            button.dataset.step = String(step);
            const hasText = fragment && fragment.text.trim() !== "";
            button.textContent = hasText ? fragment.text : `Indice manquant (#${step})`;
            if (hasText) {
                button.draggable = true;
                button.addEventListener("dragstart", (event) => {
                    event.dataTransfer?.setData("text/plain", String(step));
                    event.dataTransfer?.setDragImage(button, button.offsetWidth / 2, button.offsetHeight / 2);
                    this.draggedStep = step;
                });
                button.addEventListener("dragend", () => {
                    this.draggedStep = null;
                });
                button.addEventListener("click", () => this.placeStepInSlot(step));
            } else {
                button.disabled = true;
                button.classList.add("is-missing");
            }
            this.poolTarget.append(button);
        });
    }

    renderSlots() {
        this.state.slots.forEach((step, index) => {
            const li = this.slotElements[index];
            const textEl = li.querySelector(".finale-slot__text");
            if (!textEl) return;
            if (typeof step === "number") {
                const fragment = this.fragmentMap.get(step);
                if (fragment && fragment.text.trim() !== "") {
                    textEl.textContent = fragment.text;
                    li.classList.add("is-filled");
                } else {
                    textEl.textContent = "—";
                    li.classList.add("is-missing");
                }
            } else {
                textEl.textContent = "…";
                li.classList.remove("is-filled", "is-missing");
            }
        });
    }

    placeStepInSlot(step, index = null) {
        if (!this.fragmentMap.has(step)) return;
        if (index === null) {
            index = this.nextEmptySlot();
        }
        if (index === null) {
            this.showFeedback("Tous les emplacements sont remplis.", "warning");
            return;
        }
        const currentIndex = this.state.slots.indexOf(step);
        if (currentIndex !== -1) {
            this.state.slots[currentIndex] = null;
        }
        this.state.pool = this.state.pool.filter((s) => s !== step);
        if (typeof this.state.slots[index] === "number") {
            this.state.pool.push(this.state.slots[index]);
        }
        this.state.slots[index] = step;
        this.renderPool();
        this.renderSlots();
    }

    clearSlot(index) {
        const step = this.state.slots[index];
        if (typeof step !== "number") return;
        if (!this.state.pool.includes(step)) {
            this.state.pool.push(step);
        }
        this.state.slots[index] = null;
        this.renderPool();
        this.renderSlots();
    }

    nextEmptySlot() {
        const idx = this.state.slots.findIndex((value) => value === null);
        return idx === -1 ? null : idx;
    }

    validate() {
        const slotsComplete = this.state.slots.every((value) => typeof value === "number");
        const ordered = slotsComplete && this.state.slots.every((step, idx) => step === this.expectedOrder[idx]);
        const manualValue = this.hasInputTarget ? this.inputTarget.value.trim() : "";
        const manualOk = manualValue !== "" && this.expectedNormalized !== "" && this.normalize(manualValue) === this.expectedNormalized;

        if (ordered || manualOk) {
            if (manualOk && !ordered) {
                this.state.slots = [...this.expectedOrder];
                this.state.pool = [];
                this.renderPool();
                this.renderSlots();
            }
            this.unlockFinale({ persist: true });
        } else {
            this.showFeedback("Encore un effort ! Réessaie l’ordre ou complète la phrase.", "error");
        }
    }

    unlockFinale({ persist }) {
        if (this.completed && persist) {
            return;
        }
        this.completed = true;
        this.moduleTarget?.classList.add("is-complete");
        const ms = this.computeDuration();
        if (persist) {
            try {
                localStorage.setItem(this.orderKey, JSON.stringify(this.state.slots));
                localStorage.setItem(this.durationKey, String(ms));
            } catch (err) {
                console.warn("finale_controller: unable to persist state", err);
            }
            const event = new CustomEvent("puzzle:gameover", {
                detail: { durationMs: ms },
                bubbles: true
            });
            document.dispatchEvent(event);
        }
        this.renderDuration(ms);
        this.showFeedback("Message déverrouillé !", "success");
        const text = (this.finalRevealValue || "").trim() || this.expectedPhrase || "Bravo !";
        if (this.hasRevealTextTarget) {
            this.revealTextTarget.textContent = text;
        }
        if (this.hasFinalTimeTarget) {
            this.finalTimeTarget.textContent = ms > 0
                ? `Temps total : ${this.formatDuration(ms)}`
                : "Temps total indisponible";
        }
        if (this.hasRevealTarget) {
            this.revealTarget.hidden = false;
        }
        if (this.hasCelebrationTarget && !this.celebrationTarget.querySelector("canvas")) {
            const canvas = document.createElement("canvas");
            canvas.dataset.controller = "confetti";
            canvas.className = "confetti-canvas";
            this.celebrationTarget.append(canvas);
        }
    }

    restoreProgress() {
        try {
            const stored = localStorage.getItem(this.orderKey);
            if (stored) {
                const parsed = JSON.parse(stored);
                if (Array.isArray(parsed) && parsed.length === this.totalStepsValue) {
                    this.state.slots = parsed.map((value) => (typeof value === "number" ? value : null));
                    const used = new Set(this.state.slots.filter((value) => typeof value === "number"));
                    this.state.pool = this.expectedOrder.filter((step) => !used.has(step));
                    this.renderPool();
                    this.renderSlots();
                    this.unlockFinale({ persist: false });
                    return;
                }
            }
        } catch (err) {
            console.warn("finale_controller: unable to restore state", err);
        }
        this.shuffle();
    }

    updateDuration() {
        const stored = parseInt(localStorage.getItem(this.durationKey) || "0", 10);
        if (stored > 0) {
            this.renderDuration(stored);
        }
    }

    renderDuration(ms) {
        if (this.hasDurationTarget) {
            this.durationTarget.textContent = ms > 0 ? this.formatDuration(ms) : "—";
        }
    }

    computeDuration() {
        const stored = parseInt(localStorage.getItem(this.durationKey) || "0", 10);
        if (stored > 0) {
            return stored;
        }
        const started = parseInt(localStorage.getItem(this.startedKey) || "0", 10);
        const now = Date.now();
        if (started > 0 && now >= started) {
            return now - started;
        }
        return 0;
    }

    showFeedback(message, tone = "") {
        if (!this.hasFeedbackTarget) return;
        this.feedbackTarget.textContent = message;
        this.feedbackTarget.className = "finale-feedback";
        if (tone) {
            this.feedbackTarget.classList.add(`is-${tone}`);
        }
    }

    formatDuration(ms) {
        const totalSeconds = Math.max(0, Math.round(ms / 1000));
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        const hours = Math.floor(minutes / 60);
        const minutesDisplay = minutes % 60;
        if (hours > 0) {
            return `${hours} h ${minutesDisplay.toString().padStart(2, "0")} min ${seconds.toString().padStart(2, "0")} s`;
        }
        return `${minutes} min ${seconds.toString().padStart(2, "0")} s`;
    }

    normalize(text) {
        return text
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .replace(/[^a-z0-9]+/g, "");
    }

    escapeHtml(str) {
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
}
