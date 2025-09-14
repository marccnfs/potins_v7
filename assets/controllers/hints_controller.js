import { Controller } from "@hotwired/stimulus";

/**
 * Révèle les indices un par un.
 * Mémorise la progression dans localStorage: key = eg:{slug}:step:{step}:hintsShown
 */
export default class extends Controller {
    static values = {
        slug: String,
        step: Number,
        total: Number
    }
    static targets = ["hint", "counter"]

    connect(){
        this.key = `eg:${this.slugValue}:step:${this.stepValue}:hintsShown`;
        const shown = this._getShownCount();
        this._applyShown(shown);
        this._updateCounter();
    }

    revealNext(){
        let shown = this._getShownCount();
        if (shown >= this.hintTargets.length) return;

        shown += 1;
        this._setShownCount(shown);
        this._applyShown(shown);
        this._updateCounter();

        // petit retour visuel (toast si présent)
        const stack = document.querySelector(".toast-stack");
        if (stack) {
            const el = document.createElement("div");
            el.className = "toast";
            el.textContent = "💡 Indice dévoilé";
            stack.appendChild(el);
            setTimeout(()=> el.remove(), 2000);
        }
    }

    _applyShown(count){
        this.hintTargets.forEach((li, i) => {
            li.hidden = i >= count;
        });
    }

    _updateCounter(){
        if (!this.hasCounterTarget) return;
        const total = this.hintTargets.length;
        const shown = this._getShownCount();
        this.counterTarget.textContent = `(${shown}/${total})`;
    }

    _getShownCount(){
        const v = localStorage.getItem(this.key);
        const n = v ? parseInt(v, 10) : 0;
        return Number.isFinite(n) ? Math.max(0, Math.min(n, this.hintTargets.length)) : 0;
    }

    _setShownCount(n){
        localStorage.setItem(this.key, String(n));
    }
}
