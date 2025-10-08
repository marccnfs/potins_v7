import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["field", "list", "template", "input", "counter"];
    static values = {
        min: Number,
        placeholder: String
    };

    connect() {
        this.min = this.hasMinValue ? Number(this.minValue) : 1;
        if (!Number.isFinite(this.min) || this.min < 1) {
            this.min = 1;
        }
        this.placeholderText = this.hasPlaceholderValue ? this.placeholderValue : "Indice";

        const raw = (this.fieldTarget.value || "").trim();
        const hints = this._parse(raw);
        if (hints.length === 0) {
            hints.push("");
        }
        hints.forEach(value => this._appendRow(value));
        this._syncField();
    }

    add(event) {
        event?.preventDefault();
        this._appendRow("");
        this._focusLastInput();
        this._syncField();
    }

    remove(event) {
        event?.preventDefault();
        const button = event?.currentTarget;
        const row = button ? button.closest("[data-hint-row]") : null;
        if (!row) {
            return;
        }
        if (this.inputTargets.length <= this.min) {
            const input = row.querySelector("input");
            if (input) {
                input.value = "";
                input.focus();
            }
        } else {
            row.remove();
        }
        this._syncField();
    }

    sync() {
        this._syncField();
    }

    maybeAddFromEnter(event) {
        if (event.key !== "Enter") {
            return;
        }
        event.preventDefault();
        const input = event.currentTarget;
        const inputs = this.inputTargets;
        if (inputs[inputs.length - 1] === input) {
            this._appendRow("");
            this._focusLastInput();
        }
    }

    _appendRow(value) {
        const tpl = this.templateTarget.content.firstElementChild;
        if (!tpl) {
            return;
        }
        const clone = tpl.cloneNode(true);
        const input = clone.querySelector('[data-wizard-hints-target="input"]');
        if (input) {
            input.value = value || "";
            input.placeholder = this.placeholderText;
        }
        this.listTarget.appendChild(clone);
    }

    _syncField() {
        const values = this.inputTargets
            .map(input => input.value.trim())
            .filter(value => value.length > 0);
        this.fieldTarget.value = values.length ? JSON.stringify(values, null, 2) : "";
        this._updateCounter(values.length);
    }

    _updateCounter(validCount) {
        if (!this.hasCounterTarget) {
            return;
        }
        const total = this.inputTargets.length;
        const count = typeof validCount === "number"
            ? validCount
            : this.inputTargets.filter(input => input.value.trim().length > 0).length;
        this.counterTarget.textContent = `${count}/${total}`;
    }

    _focusLastInput() {
        const inputs = this.inputTargets;
        if (inputs.length === 0) {
            return;
        }
        const last = inputs[inputs.length - 1];
        if (last) {
            last.focus();
        }
    }

    _parse(raw) {
        if (!raw) {
            return [];
        }
        try {
            const decoded = JSON.parse(raw);
            if (Array.isArray(decoded)) {
                return decoded.map(item => {
                    if (typeof item === "string") {
                        return item;
                    }
                    if (item === null || item === undefined) {
                        return "";
                    }
                    return String(item);
                }).filter(Boolean);
            }
        } catch (error) {
            // ignore JSON errors, fallback below
        }
        return raw.split(/\r?\n/).map(value => value.trim()).filter(Boolean);
    }
}
