import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = { view: String, date: String }
    static targets = ["datePicker", "headline"]

    connect() {
        this.dateValue = this.normalizeDateForView(this.viewValue, this.dateValue);
        this.refreshHeadline();
        if (this.hasDatePickerTarget) {
            this.datePickerTarget.value = this.dateValue;
            this.datePickerTarget.addEventListener('change', () => {
                this.dateValue = this.normalizeDateForView(this.viewValue, this.datePickerTarget.value);
                this.syncCalendar();
            });
        }
        this.syncCalendar();
    }

    prev() {
        const d = this.parseDate(this.dateValue);
        if (this.viewValue === 'month') d.setMonth(d.getMonth()-1);
        else if (this.viewValue === 'week') d.setDate(d.getDate()-7);
        else d.setDate(d.getDate()-1);
        this.dateValue = this.normalizeDateForView(this.viewValue, this.format(d));
        this.syncCalendar();
    }

    next() {
        const d = this.parseDate(this.dateValue);
        if (this.viewValue === 'month') d.setMonth(d.getMonth()+1);
        else if (this.viewValue === 'week') d.setDate(d.getDate()+7);
        else d.setDate(d.getDate()+1);
        this.dateValue = this.normalizeDateForView(this.viewValue, this.format(d));
        this.syncCalendar();
    }

    today() {
        const t = new Date();
        this.dateValue = this.normalizeDateForView(this.viewValue, this.format(t));
        this.syncCalendar();
    }

    setView(event) {
        const v = event.currentTarget.dataset.viewParam;
        if (!v) return;
        this.viewValue = v;
        this.dateValue = this.normalizeDateForView(this.viewValue, this.dateValue);
        this.syncCalendar();
    }

    syncCalendar() {
        if (this.hasDatePickerTarget) {
            this.datePickerTarget.value = this.dateValue;
        }
        this.dispatch('change', {
            detail: { view: this.viewValue, date: this.dateValue }
        });
        this.refreshHeadline();
    }

    refreshHeadline() {
        if (!this.hasHeadlineTarget) return;
        const d = this.parseDate(this.dateValue);
        const intlMonth = new Intl.DateTimeFormat('fr-FR', { month:'long', year:'numeric' });
        if (this.viewValue === 'month') {
            this.headlineTarget.textContent = intlMonth.format(d);
        } else if (this.viewValue === 'week') {
            // Semaine ISO
            const { start, end } = this.weekRange(d);
            const intl = new Intl.DateTimeFormat('fr-FR', { day: '2-digit', month: 'long' });
            this.headlineTarget.textContent = `Du ${intl.format(start)} au ${intl.format(end)}`;
        } else {
            const intlDay = new Intl.DateTimeFormat('fr-FR', { day:'2-digit', month:'long', year:'numeric' });
            this.headlineTarget.textContent = intlDay.format(d);
        }
    }

    // helpers
    format(d){ const p=n=>String(n).padStart(2,'0'); return `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())}`;}
    parseDate(str){
        if (!str) return new Date();
        const [y,m,d] = str.split('-').map(Number);
        if ([y,m,d].some(Number.isNaN)) return new Date(str);
        return new Date(y, m-1, d);
    }
    weekRange(date) {
        const monday = new Date(date);
        const day = (monday.getDay()+6)%7; // lundi=0
        monday.setDate(monday.getDate()-day);
        const tuesday = new Date(monday);
        tuesday.setDate(monday.getDate()+1);
        const saturday = new Date(tuesday);
        saturday.setDate(tuesday.getDate()+4);
        return { start: tuesday, end: saturday };
    }
    normalizeDateForView(view, dateStr) {
        const parsed = this.parseDate(dateStr);
        if (view === 'week') {
            const { start } = this.weekRange(parsed);
            return this.format(start);
        }
        return this.format(parsed);
    }
}
