import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = {
        fetchUrl: String,
        date: String,
        requestUrl: String,
        context: String,
        manage: Boolean,
        showRequests: Boolean,
    }

    static targets = ["grid", "headline"]

    connect() {
        this.boundCloseMenu = this.handleDocumentClick.bind(this);
        document.addEventListener('click', this.boundCloseMenu);
        this.load();
    }

    disconnect() {
        document.removeEventListener('click', this.boundCloseMenu);
    }

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
        const communeColor = this.communeColor(e.communeColor);
        const textColor = this.textColorFor(communeColor);
        const categoryLabel = this.categoryLabel(e);
        const requestable = this.shouldShowRequests() && this.isRequestable(e);
        const requestUrl = requestable ? this.requestUrl(e) : null;
        const manageEnabled = this.manageEnabled() && e.manage;
        const manageMenu = manageEnabled ? this.renderManageMenu(e.manage) : '';
        const attrs = [
            'class="event-pill"',
            `style="--commune-color:${communeColor}; --commune-text:${textColor};"`
        ];
        if (manageEnabled) {
            attrs.push('data-action="click->board-week#toggleEventMenu"');
            attrs.push('data-has-menu="true"');
        }
        return `
     <div ${attrs.join(' ')}>
      <div class="event-title">${e.title} ${categoryLabel ? `<span class="badge">${categoryLabel}</span>` : ''}</div>
      <div class="event-meta">${e.isAllDay ? 'Toute la journée' : `${e.startsAtLocal} — ${e.endsAtLocal}`}${e.locationName ? ` • ${e.locationName}` : ''}</div>
      ${requestable ? `<div class="event-actions"><a class="btn-ghost" href="${requestUrl}">Prendre RDV</a></div>` : ''}
    </div>`;
    }

    renderManageMenu(manage) {
        const edit = manage.editUrl ? `<a class="btn btn-light" href="${manage.editUrl}">Modifier</a>` : '';
        const duplicate = manage.duplicateUrl ? `
            <form method="post" action="${manage.duplicateUrl}" class="event-manage-form" data-action="submit->board-week#closeMenus">
                <input type="hidden" name="_token" value="${manage.duplicateToken}">
                <button type="submit" class="btn btn-light">Dupliquer</button>
            </form>` : '';
        const remove = manage.deleteUrl ? `
            <form method="post" action="${manage.deleteUrl}" class="event-manage-form" data-action="submit->board-week#confirmDelete">
                <input type="hidden" name="_token" value="${manage.deleteToken}">
                <button type="submit" class="btn btn-danger">Supprimer</button>
            </form>` : '';

        return `
      <div class="event-manage-menu" data-board-week-menu hidden>
        <p class="event-manage-label">Gérer ce créneau</p>
        <div class="event-manage-actions">
          ${edit}
          ${duplicate}
          ${remove}
        </div>
      </div>`;
    }

    communeColor(raw) {
        return raw || '#f3f4f6';
    }

    categoryLabel(event) {
        return event.categoryLabel || this.humanCategory(event.category);
    }

    humanCategory(raw) {
        switch ((raw || '').toLowerCase()) {
            case 'rdv': return 'RDV public';
            case 'atelier': return 'Atelier collectif';
            case 'permanence': return 'Permanence';
            case 'formation': return 'Formation';
            case 'indispo': return 'Indisponible';
            case 'externe': return 'Événement externe';
            case 'autre': return 'Autre activité';
            default: return '';
        }
    }

    isRequestable(event) {
        if (typeof event.canRequest === 'boolean') {
            return event.canRequest;
        }
        const raw = (event.category || '').toLowerCase();
        return ['rdv', 'atelier', 'permanence'].includes(raw);
    }

    requestUrl(event) {
        if (event.requestUrl) {
            return event.requestUrl;
        }
        if (this.requestUrlValue) {
            return this.requestUrlValue.replace('__SLUG__', encodeURIComponent(event.slug));
        }
        return `/rdv/${encodeURIComponent(event.slug)}`;
    }

    shouldShowRequests() {
        return !this.hasShowRequestsValue || this.showRequestsValue;
    }

    manageEnabled() {
        return this.hasManageValue && this.manageValue;
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
    toggleEventMenu(event) {
        const target = event.currentTarget;
        if (!target) return;
        if (event.target.closest('.event-manage-menu')) {
            return;
        }
        const menu = target.querySelector('.event-manage-menu');
        if (!menu) return;
        const willOpen = menu.hasAttribute('hidden');
        this.closeMenus();
        if (willOpen) {
            menu.removeAttribute('hidden');
        }
    }

    closeMenus() {
        this.element.querySelectorAll('.event-manage-menu').forEach(menu => menu.setAttribute('hidden', ''));
    }

    handleDocumentClick(event) {
        if (!this.element.contains(event.target)) {
            this.closeMenus();
        }
    }

    confirmDelete(event) {
        if (!confirm('Confirmer la suppression de ce créneau ?')) {
            event.preventDefault();
            event.stopPropagation();
            return;
        }
        this.closeMenus();
    }

    textColorFor(hex) {
        const color = (hex || '').replace('#', '');
        if (color.length !== 6) {
            return '#111827';
        }
        const r = parseInt(color.substring(0,2), 16) / 255;
        const g = parseInt(color.substring(2,4), 16) / 255;
        const b = parseInt(color.substring(4,6), 16) / 255;
        const luminance = 0.2126 * this.linearize(r) + 0.7152 * this.linearize(g) + 0.0722 * this.linearize(b);
        return luminance > 0.55 ? '#111827' : '#ffffff';
    }

    linearize(value) {
        return value <= 0.03928 ? value / 12.92 : Math.pow((value + 0.055) / 1.055, 2.4);
    }
}
