import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = { view: String, date: String, fetchUrl: String }
    static targets = ["grid"]

    connect() {
        this.load();
        this.element.addEventListener('agenda-nav:change', (e) => {
            const { view, date } = e.detail;
            this.viewValue = view;
            this.dateValue = date;
            this.load();
        });
        document.addEventListener('agenda-filters:changed', () => this.load());
    }

    async load() {
        const { from, to } = this.range(this.viewValue, this.dateValue);
        const url = new URL(this.fetchUrlValue, window.location.origin);
        url.searchParams.set('from', from);
        url.searchParams.set('to', to);

        const cat = this.currentCategory();
        if (cat) url.searchParams.set('category', cat);

        const res = await fetch(url.toString());
        const events = await res.json();
        this.render(events);
    }

    render(events) {
        if (!Array.isArray(events) || !events.length) {
            this.gridTarget.innerHTML = `<p class="muted">Aucun événement.</p>`;
            return;
        }
        this.gridTarget.innerHTML =
            `<ul class="agenda-ul">` + events.map(e => `
        <li class="event">
          <a href="/events/${e.slug}">
            <div class="time">${e.isAllDay ? 'Toute la journée' : `${e.startsAtLocal} — ${e.endsAtLocal}`}</div>
            <div class="title">${e.title}</div>
            ${e.locationName ? `<div class="loc">${e.locationName}</div>` : ``}
            <span class="badge">${e.category}</span>
          </a>
        </li>`
            ).join('') + `</ul>`;
    }

    range(view, dateStr) {
        const base = new Date(dateStr);
        const pad = (n)=>String(n).padStart(2,'0');
        const fmt = (d)=>`${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;

        let start = new Date(base), end = new Date(base);
        if (view === 'month') {
            start = new Date(base.getFullYear(), base.getMonth(), 1);
            end   = new Date(base.getFullYear(), base.getMonth()+1, 0);
        } else if (view === 'week') {
            const day = (base.getDay()+6)%7; // lundi=0
            start.setDate(base.getDate()-day);
            end.setDate(start.getDate()+6);
        }
        return { from: fmt(start), to: fmt(end) };
    }

    currentCategory() {
        const el = document.querySelector('[data-controller="agenda-filters"] select');
        return el ? el.value : '';
    }
}
