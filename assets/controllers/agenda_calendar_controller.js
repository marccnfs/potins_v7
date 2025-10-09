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
        this.currentRange = { from, to };
        const url = new URL(this.fetchUrlValue, window.location.origin);
        url.searchParams.set('from', from);
        url.searchParams.set('to', to);

        const cat = this.currentCategory();
        if (cat) url.searchParams.set('category', cat);

        try {
            const res = await fetch(url.toString());
            if (!res.ok) throw new Error(res.statusText);
            const events = await res.json();
            this.render(events);
        } catch (err) {
            console.error('Agenda load error', err);
            this.gridTarget.innerHTML = `<p class="muted">Erreur lors du chargement des événements.</p>`;
            document.querySelector('.toast-stack')?.controller?.push('Impossible de charger les événements.');
        }
    }

    render(events) {
        if (this.viewValue === 'week') {
            this.renderWeek(Array.isArray(events) ? events : []);
            return;
        }
        if (!Array.isArray(events) || !events.length) {
            this.gridTarget.innerHTML = `<p class="muted">Aucun événement.</p>`;
            return;
        }
        this.gridTarget.innerHTML =
            `<ul class="agenda-ul">` + events.map(e => this.renderEventItem(e)).join('') + `</ul>`;
    }

    range(view, dateStr) {
        const base = this.parseDate(dateStr);
        const pad = (n)=>String(n).padStart(2,'0');
        const fmt = (d)=>`${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;

        let start = new Date(base), end = new Date(base);
        if (view === 'month') {
            start = new Date(base.getFullYear(), base.getMonth(), 1);
            end   = new Date(base.getFullYear(), base.getMonth()+1, 0);
        } else if (view === 'week') {
            const day = (base.getDay()+6)%7; // lundi=0
            start.setDate(base.getDate()-day + 1); // mardi
            end.setDate(start.getDate()+4); // samedi
        }
        return { from: fmt(start), to: fmt(end) };
    }

    currentCategory() {
        const el = document.querySelector('[data-controller="agenda-filters"] select');
        return el ? el.value : '';
    }

    renderEventItem(e) {
        return `
        <li class="event">
          <a href="/events/${e.slug}">
            <div class="time">${e.isAllDay ? 'Toute la journée' : `${e.startsAtLocal} — ${e.endsAtLocal}`}</div>
            <div class="title">${e.title}</div>
            ${e.locationName ? `<div class="loc">${e.locationName}</div>` : ``}
            <span class="badge">${e.category}</span>
          </a>
        </li>`;
    }

    renderWeek(events) {
        const scheduleDefinition = [
            { weekday: 2, label: 'Mardi', slots: [
                    { id: 'am', label: '09h00 – 12h30', start: '09:00', end: '12:30' },
                    { id: 'pm', label: '14h00 – 18h30', start: '14:00', end: '18:30' },
                ]},
            { weekday: 3, label: 'Mercredi', slots: [
                    { id: 'am', label: '09h00 – 12h30', start: '09:00', end: '12:30' },
                    { id: 'pm', label: '14h00 – 18h30', start: '14:00', end: '18:30' },
                ]},
            { weekday: 4, label: 'Jeudi', slots: [
                    { id: 'am', label: '09h00 – 12h30', start: '09:00', end: '12:30' },
                    { id: 'pm', label: '14h00 – 18h30', start: '14:00', end: '18:30' },
                ]},
            { weekday: 5, label: 'Vendredi', slots: [
                    { id: 'am', label: '09h00 – 12h30', start: '09:00', end: '12:30' },
                    { id: 'pm', label: '14h00 – 18h30', start: '14:00', end: '18:30' },
                ]},
            { weekday: 6, label: 'Samedi', note: 'Créneau événements Potins numériques', slots: [
                    { id: 'potins', label: '10h00 – 12h00', start: '10:00', end: '12:00' },
                ]},
        ];

        const startDate = this.currentRange ? this.parseDate(this.currentRange.from) : this.parseDate(this.dateValue);
        const intlDay = new Intl.DateTimeFormat('fr-FR', { day: '2-digit', month: 'long' });

        const days = scheduleDefinition.map((def, idx) => {
            const date = new Date(startDate);
            date.setDate(startDate.getDate() + idx);
            return {
                ...def,
                date,
                slots: def.slots.map(slot => ({ ...slot, events: [] })),
                extra: [],
            };
        });

        const outside = [];
        events.forEach((event) => {
            const weekday = Number(event.weekday);
            const day = days.find(d => d.weekday === weekday);
            if (!day) {
                outside.push(event);
                return;
            }
            if (event.isAllDay) {
                day.extra.push(event);
                return;
            }
            const slot = day.slots.find(s => this.eventFitsSlot(event, s));
            if (slot) {
                slot.events.push(event);
            } else {
                day.extra.push(event);
            }
        });

        const html = [`<div class="agenda-week">`];
        days.forEach(day => {
            html.push(`
            <section class="agenda-day">
                <header class="agenda-day__header">
                    <span class="agenda-day__name">${day.label}</span>
                    <span class="agenda-day__date">${intlDay.format(day.date)}</span>
                </header>
            `);
            day.slots.forEach(slot => {
                html.push(`
                <div class="agenda-slot">
                    <div class="agenda-slot__header">
                        <h3>${slot.label}</h3>
                        ${day.note && slot.id === 'potins' ? `<p class="agenda-slot__note">${day.note}</p>` : ''}
                    </div>
                    ${slot.events.length ? `<ul class="agenda-slot__list">${slot.events.map(e => this.renderWeekEvent(e)).join('')}</ul>` : `<p class="agenda-slot__empty muted">Aucun rendez-vous planifié.</p>`}
                </div>`);
            });
            if (day.extra.length) {
                html.push(`
                <div class="agenda-slot agenda-slot--extra">
                    <div class="agenda-slot__header">
                        <h3>Autres créneaux</h3>
                    </div>
                    <ul class="agenda-slot__list">${day.extra.map(e => this.renderWeekEvent(e)).join('')}</ul>
                </div>`);
            }
            html.push(`</section>`);
        });

        if (outside.length) {
            html.push(`
            <section class="agenda-day agenda-day--outside">
                <header class="agenda-day__header">
                    <span class="agenda-day__name">Hors créneaux hebdomadaires</span>
                </header>
                <ul class="agenda-slot__list">${outside.map(e => this.renderWeekEvent(e)).join('')}</ul>
            </section>`);
        }

        html.push(`</div>`);
        this.gridTarget.innerHTML = html.join('');
    }

    renderWeekEvent(event) {
        const time = event.isAllDay
            ? 'Toute la journée'
            : `${this.formatHour(event.startsAtTime)} – ${this.formatHour(event.endsAtTime)}`;
        return `
            <li class="event">
                <a href="/events/${event.slug}">
                    <div class="time">${time}</div>
                    <div class="title">${event.title}</div>
                    ${event.locationName ? `<div class="loc">${event.locationName}</div>` : ''}
                    <span class="badge">${event.category}</span>
                </a>
            </li>`;
    }

    eventFitsSlot(event, slot) {
        const start = this.timeToMinutes(event.startsAtTime);
        const end = this.timeToMinutes(event.endsAtTime);
        const slotStart = this.timeToMinutes(slot.start);
        const slotEnd = this.timeToMinutes(slot.end);
        return Number.isFinite(start) && Number.isFinite(end) && start < slotEnd && end > slotStart;
    }

    timeToMinutes(timeStr) {
        if (!timeStr || typeof timeStr !== 'string') return NaN;
        const [h, m] = timeStr.split(':').map(Number);
        if (Number.isNaN(h) || Number.isNaN(m)) return NaN;
        return h * 60 + m;
    }

    formatHour(timeStr) {
        if (!timeStr) return '';
        const [h, m] = timeStr.split(':');
        return `${h}h${m}`;
    }

    parseDate(dateStr) {
        if (!dateStr) return new Date();
        const [y, m, d] = dateStr.split('-').map(Number);
        if ([y, m, d].some(Number.isNaN)) return new Date(dateStr);
        return new Date(y, m - 1, d);
    }
}
