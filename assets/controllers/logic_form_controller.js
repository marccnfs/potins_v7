// assets/controllers/logic_form_controller.js
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["container", "button", "msg"];
    static values  = {
        questions: Array,         // injecté via data-logic-form-questions-value='[...]'
        okMessage: String,
        failMessage: String
    }

    connect() {
        const qs = Array.isArray(this.questionsValue) ? this.questionsValue : [];
        this.render(qs);
    }

    render(qs) {
        const c = this.containerTarget;
        c.innerHTML = "";

        if (!qs.length) {
            this.buttonTarget.disabled = true;
            c.innerHTML = `<p class="muted">Aucune question définie.</p>`;
            return;
        }
        this.buttonTarget.disabled = false;

        qs.forEach((q, qi) => {
            const box = document.createElement("section");
            box.className = "card";
            const label = (q?.label ?? `Question ${qi+1}`).toString();

            const opts = Array.isArray(q?.options) ? q.options : [];
            const items = opts.map(o => {
                const id = (o?.id ?? "").toString();
                const lab = (o?.label ?? "").toString();
                if (!id || !lab) return "";
                return `<label style="display:block;margin:.25rem 0;">
          <input type="checkbox" name="q${qi}[]" value="${this.escape(id)}"> ${this.escape(lab)}
        </label>`;
            }).join("");

            box.innerHTML = `<h3>${this.escape(label)}</h3>${items || "<p class='muted'>Aucune option.</p>"}`;
            c.appendChild(box);
        });
    }

    submit() {
        const qs = Array.isArray(this.questionsValue) ? this.questionsValue : [];
        if (!qs.length) return;

        let allGood = true;

        qs.forEach((q, qi) => {
            const must    = Array.isArray(q?.solution?.must)    ? q.solution.must.map(String)    : [];
            const mustNot = Array.isArray(q?.solution?.mustNot) ? q.solution.mustNot.map(String) : [];

            const checked = Array.from(this.element.querySelectorAll(`input[name="q${qi}[]"]:checked`))
                .map(i => i.value);

            // valid si tous les 'must' sont cochés et aucun 'mustNot' cochés
            const okMust    = must.every(id => checked.includes(id));
            const okMustNot = mustNot.every(id => !checked.includes(id));
            if (!(okMust && okMustNot)) allGood = false;
        });

        this.msgTarget.textContent = allGood ? (this.okMessageValue || "Bravo !")
            : (this.failMessageValue || "Réessaie.");
        this.msgTarget.className = allGood ? "ok" : "fail";

        if (allGood) {
            // Notifie le système (score, progression…)
            document.dispatchEvent(new CustomEvent('puzzle:solved', { bubbles:true, detail:{ type:'logic_form' }}));
        }
    }

    escape(s) {
        return s.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }
}
