import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = { view: String, date: String }
    static targets = ["datePicker", "headline"]

    connect() {
        this.refreshHeadline();
        if (this.hasDatePickerTarget) {
            this.datePickerTarget.value = this.dateValue;
            this.datePickerTarget.addEventListener('change', () => {
                this.dateValue = this.datePickerTarget.value;
                this.syncCalendar();
            });
        }
        this.syncCalendar();
    }

    prev() {
        const d = new Date(this.dateValue);
        if (this.viewValue === 'month') d.setMonth(d.getMonth()-1);
        else if (this.viewValue === 'week') d.setDate(d.getDate()-7);
        else d.setDate(d.getDate()-1);
        this.dateValue = this.format(d);
        this.syncCalendar();
    }

    next() {
        const d = new Date(this.dateValue);
        if (this.viewValue === 'month') d.setMonth(d.getMonth()+1);
        else if (this.viewValue === 'week') d.setDate(d.getDate()+7);
        else d.setDate(d.getDate()+1);
        this.dateValue = this.format(d);
        this.syncCalendar();
    }

    today() {
        const t = new Date();
        this.dateValue = this.format(t);
        this.syncCalendar();
    }

    setView(event) {
        const v = event.currentTarget.dataset.viewParam;
        if (!v) return;
        this.viewValue = v;
        this.syncCalendar();
    }

    syncCalendar() {
        this.dispatch('change', {
            detail: { view: this.viewValue, date: this.dateValue }
        });
        this.refreshHeadline();
    }

    refreshHeadline() {
        if (!this.hasHeadlineTarget) return;
        const d = new Date(this.dateValue);
        const intlMonth = new Intl.DateTimeFormat('fr-FR', { month:'long', year:'numeric' });
        if (this.viewValue === 'month') {
            this.headlineTarget.textContent = intlMonth.format(d);
        } else if (this.viewValue === 'week') {
            // Semaine ISO
            const week = this.isoWeek(d);
            this.headlineTarget.textContent = `Semaine ${week}, ${d.getFullYear()}`;
        } else {
            const intlDay = new Intl.DateTimeFormat('fr-FR', { day:'2-digit', month:'long', year:'numeric' });
            this.headlineTarget.textContent = intlDay.format(d);
        }
    }

    // helpers
    format(d){ const p=n=>String(n).padStart(2,'0'); return `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())}`;}
    isoWeek(d) {
        const t = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
        const dayNum = t.getUTCDay() || 7;
        t.setUTCDate(t.getUTCDate() + 4 - dayNum);
        const yearStart = new Date(Date.UTC(t.getUTCFullYear(),0,1));
        return Math.ceil((((t - yearStart) / 86400000) + 1)/7);
    }
}
