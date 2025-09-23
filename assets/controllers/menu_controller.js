// assets/controllers/menu_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['trigger', 'panel', 'overlay', 'close', 'firstLink'];

    connect() {
        this._escHandler = (e) => {
            if (e.key === 'Escape') this.close();
        };
        this._trapHandler = (e) => this.trapFocus(e);
    }

    toggle() {
        this.isOpen ? this.close() : this.open();
    }

    open() {
        if (this.isOpen) return;

        // montrer
        this.panelTarget.hidden = false;
        this.overlayTarget.hidden = false;
        this.panelTarget.setAttribute('aria-hidden', 'false');
        this.triggerTarget.setAttribute('aria-expanded', 'true');

        // lock scroll
        document.documentElement.classList.add('is-locked');
        document.body.classList.add('is-locked');

        // focus
        this._previousActive = document.activeElement;
        const focusEl = this.closeTarget || this.firstFocusable(this.panelTarget) || this.panelTarget;
        focusEl.focus();

        // listeners
        document.addEventListener('keydown', this._escHandler, { passive: true });
        document.addEventListener('keydown', this._trapHandler);

        this.element.classList.add('is-open');
    }

    close() {
        if (!this.isOpen) return;

        // cacher
        this.panelTarget.hidden = true;
        this.overlayTarget.hidden = true;
        this.panelTarget.setAttribute('aria-hidden', 'true');
        this.triggerTarget.setAttribute('aria-expanded', 'false');

        // unlock scroll
        document.documentElement.classList.remove('is-locked');
        document.body.classList.remove('is-locked');

        // restore focus
        try { this._previousActive?.focus?.(); } catch (_) {}

        // listeners
        document.removeEventListener('keydown', this._escHandler);
        document.removeEventListener('keydown', this._trapHandler);

        this.element.classList.remove('is-open');
    }

    get isOpen() {
        return this.panelTarget.hidden === false;
    }

    // Focus utilities
    focusables(root) {
        return [...root.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )].filter(el => el.offsetParent !== null || el === document.activeElement);
    }

    firstFocusable(root) {
        return this.focusables(root)[0];
    }

    trapFocus(e) {
        if (!this.isOpen || e.key !== 'Tab') return;
        const items = this.focusables(this.panelTarget);
        if (items.length === 0) return;

        const first = items[0];
        const last = items[items.length - 1];

        if (e.shiftKey && document.activeElement === first) {
            last.focus();
            e.preventDefault();
        } else if (!e.shiftKey && document.activeElement === last) {
            first.focus();
            e.preventDefault();
        }
    }
}
