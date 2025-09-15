import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = { fetchUrl: String, date: String }
    static targets = ["grid", "headline"]

    connect() { this.load(); }

    prevWeek() { this.shiftDays(-7); }
    nextWeek() { this.shiftDays(7); }
    thisWeek() { this.dateValue = this.format(new Date()); this.load(); }

    async load() {
        const { monday, saturday } = this.weekRange(new Date(this.dateValue));
        this.updateHeadline(monday);

        const url = new URL(this.fetchUrlValue, window.location.origin);
        url.searchParams.set('from', this.fmtDate(monday));
        url.searchParams.set('to', this.fmtDate(saturday));

        try {
            const res = await fetch(url.toString());
            if (!res.ok) throw new Error(res.statusText);
            const events = await res.json();

            // structure: {2:{am:[], pm:[]}, 3:{...}, 4:{...}, 5:{...}, 6:{...}} -> Tue..Sat
            const byDay = {2: {am:[], pm:[]}, 3:{am:[], pm:[]}, 4:{am:[], pm:[]}, 5:{am:[], pm:[]}, 6:{am:[], pm:[]}};

            (events || []).forEach(e => {
                // parse "dd/mm HH:ii" venant du feed
                const [ds, hs] = (e.startsAtLocal || '').split(' ');
                const day = this.dayOfWeekFromDateString(ds); // 0=Sun..6=Sat
                if (day < 2 || day > 6) return; // on garde Tue..Sat

                const isMorning = this.isMorning(hs);
                const slot = isMorning ? 'am' : 'pm';

                byDay[day][slot].push(e);
            });

            this.render(byDay);
        } catch (err) {
            console.error('BoardWeek load error', err);
            this.gridTarget.innerHTML = `<p class="muted">Erreur lors du chargement des événements.</p>`;
            document.querySelector('.toast-stack')?.controller?.push('Impossible de charger les événements.');
        }
    }

    render(byDay) {
        const dayNames = {2:'Mardi',3:'Mercredi',4:'Jeudi',5:'Vendredi',6:'Samedi'};
        const html = [2,3,4,5,6].map(dow => {
            const am = byDay[dow].am.map(e => this.renderEvent(e)).join('');
            const pm = byDay[dow].pm.map(e => this.renderEvent(e)).join('');
            return `
        <div class="day-card">
          <h2>${dayNames[dow]}</h2>
          <div class="slot">
            <div class="slot-title"><strong>Matin</strong></div>
            ${am || `<div class="event-meta">—</div>`}
          </div>
          <div class="slot">
            <div class="slot-title"><strong>Après-midi</strong></div>
            ${pm || `<div class="event-meta">—</div>`}
          </div>
        </div>`;
        }).join('');

        this.gridTarget.innerHTML = html;
    }

    renderEvent(e) {
        const communeClass = this.communeClass(e.commune); // <-- direct depuis feed
        return `
    <div class="event-pill ${communeClass}">
      <div class="event-title">${e.title} ${e.category ? `<span class="badge">${e.category}</span>` : ''}</div>
      <div class="event-meta">${e.isAllDay ? 'Toute la journée' : `${e.startsAtLocal} — ${e.endsAtLocal}`}${e.locationName?` • ${e.locationName}`:''}</div>
      <div class="event-actions">
        <a class="btn-ghost" href="/rdv?event=${encodeURIComponent(e.slug)}">Prendre RDV</a>
      </div>
    </div>`;
    }

    communeClass(code) {
        switch ((code || 'autre').toLowerCase()) {
            case 'pellerin': return 'c-pellerin';
            case 'montagne': return 'c-montagne';
            case 'sjb':      return 'c-sjb';
            default:         return 'c-autre';
        }
    }

    // helpers
    weekRange(d) {
        const day = (d.getDay()+6)%7; // lundi=0
        const monday = new Date(d); monday.setDate(d.getDate()-day);
        const saturday = new Date(monday); saturday.setDate(monday.getDate()+5);
        return { monday, saturday };
    }
    shiftDays(n) { const d=new Date(this.dateValue); d.setDate(d.getDate()+n); this.dateValue=this.format(d); this.load(); }
    updateHeadline(monday) {
        const intl = new Intl.DateTimeFormat('fr-FR', { day:'2-digit', month:'long' });
        const sat = new Date(monday); sat.setDate(monday.getDate()+5);
        if (this.hasHeadlineTarget) this.headlineTarget.textContent = `Semaine du ${intl.format(monday)} au ${intl.format(sat)}`;
    }
    fmtDate(d){ const p=n=>String(n).padStart(2,'0'); return `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())}`; }
    format(d){ return this.fmtDate(d); }
    dayOfWeekFromDateString(fr) { // 'dd/mm'
        const [dd, mm] = fr.split('/');
        const y = (new Date()).getFullYear();
        const d = new Date(`${y}-${mm}-${dd}T00:00:00`);
        return d.getDay(); // 0..6
    }
    isMorning(hhmm) {
        if (!hhmm) return true;
        const [h, m] = hhmm.split(':').map(x=>parseInt(x,10));
        return (h < 12) || (h === 12 && (m||0) < 30);
    }
}
