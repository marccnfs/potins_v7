// assets/controllers/html_min_controller.js
import { Controller } from "@hotwired/stimulus";

/**
 * Éditeur/validateur d'HTML minimal.
 * Valeurs:
 *  - starter: String (HTML de départ)
 *  - checks:  Array (règles)
 *  - okMessage: String
 *
 * Cibles:
 *  - editor: <textarea> que l'on remplit avec starter au connect()
 *  - frame:  <iframe> où l'on rend le HTML (srcdoc)
 *  - msg:    <div> messages de test
 */
export default class extends Controller {
    static values = {
        starter: String,
        checks: Array,
        okMessage: { type: String, default: "Bravo !" }
    }
    static targets = ["editor","frame","msg"]

    connect(){
        // Remplir l'éditeur si vide
        if (this.hasEditorTarget && !this.editorTarget.value.trim()) {
            this.editorTarget.value = (this.starterValue || "<!-- Écris ici -->").trim();
        }
        // Rendre une première fois
        this.render();
    }

    render(){
        if (!this.hasFrameTarget) return;
        const html = this.hasEditorTarget ? this.editorTarget.value : (this.starterValue || "");
        const doc = this.wrap(html);
        this.frameTarget.srcdoc = doc;
        if (this.hasMsgTarget) this.msgTarget.textContent = "";
    }

    test(){
        if (!this.hasFrameTarget) return;
        const html = this.hasEditorTarget ? this.editorTarget.value : (this.starterValue || "");
        const doc = this.wrap(html);
        this.frameTarget.srcdoc = doc;

        // Attendre que le navigateur installe le document
        setTimeout(()=> {
            let win, root;
            try {
                win = this.frameTarget.contentWindow;
                root = win.document;
            } catch (e) {
                this.showError("Impossible d'accéder au document rendu (sandbox).");
                return;
            }

            const checks = Array.isArray(this.checksValue) ? this.checksValue : [];
            if (!checks.length) {
                this.showInfo("Aucune règle définie pour ce puzzle.");
                return;
            }

            const results = [];
            for (const rule of checks) {
                results.push(this.runRule(rule, root));
            }

            const ok = results.every(r => r.ok);
            this.renderResults(results, ok);
            if (ok) {
                document.dispatchEvent(new CustomEvent("puzzle:solved",{bubbles:true}));
            }
        }, 0);
    }

    runRule(rule, doc){
        const type = (rule?.type || "").trim();
        switch(type){

            case "selectorExists": {
                const sel = rule.selector || "";
                const ok = !!doc.querySelector(sel);
                return { ok, label: `Existe: \`${sel}\``, detail: ok ? "" : `Aucun élément ne correspond.` };
            }

            case "textIncludes": {
                const sel = rule.selector || "";
                const text = (rule.text || "").toString();
                const el = doc.querySelector(sel);
                const ok = !!el && el.textContent.includes(text);
                return {
                    ok,
                    label: `Le texte de \`${sel}\` contient « ${text} »`,
                    detail: el ? (ok ? "" : `Trouvé: « ${el.textContent.trim()} »`) : `Sélecteur introuvable.`
                };
            }

            case "selectorCountAtLeast": {
                const sel = rule.selector || "";
                const min = Number(rule.count || 1);
                const count = doc.querySelectorAll(sel).length;
                const ok = count >= min;
                return { ok, label: `Au moins ${min} × \`${sel}\``, detail: `Actuel: ${count}` };
            }

            // 2 règles supplémentaires (facultatives mais utiles)

            case "attrEquals": {
                const sel = rule.selector || "";
                const attr = rule.attr || "";
                const val  = (rule.value ?? "").toString();
                const el = doc.querySelector(sel);
                const ok = !!el && el.getAttribute(attr) === val;
                return {
                    ok,
                    label: `\`${sel}\` a l'attribut ${attr}="${val}"`,
                    detail: el ? (ok ? "" : `Actuel: ${el?.getAttribute(attr) ?? "—"}`) : `Sélecteur introuvable.`
                };
            }

            case "htmlIncludes": {
                const frag = (rule.html || "").toString();
                const ok = doc.documentElement.outerHTML.includes(frag);
                return { ok, label: `Le HTML contient le fragment « ${frag} »`, detail: "" };
            }

            default:
                return { ok:false, label:`Type inconnu: ${type}`, detail:"" };
        }
    }

    renderResults(results, allOk){
        if (!this.hasMsgTarget) return;
        const items = results.map(r => {
            const icon = r.ok ? "✅" : "❌";
            const detail = r.detail ? `<div class="t-detail">${this.escape(r.detail)}</div>` : "";
            return `<li class="t-item ${r.ok?'ok':'ko'}">${icon} ${this.escape(r.label)}${detail}</li>`;
        }).join("");

        this.msgTarget.innerHTML = `
      <div class="t-wrap">
        <ul class="t-list">${items}</ul>
        <div class="t-final">${allOk ? "🎉 " + this.okMessageValue : "Encore un effort…"}</div>
      </div>
    `;
    }

    showError(msg){ if (this.hasMsgTarget) this.msgTarget.innerHTML = `<div class="t-error">❌ ${this.escape(msg)}</div>`; }
    showInfo(msg){ if (this.hasMsgTarget) this.msgTarget.innerHTML = `<div class="t-info">ℹ️ ${this.escape(msg)}</div>`; }

    wrap(html){
        // Anti-blank: doctype + basique head/body
        return `<!doctype html><html><head><meta charset="utf-8"><title>Preview</title></head><body>${html}</body></html>`;
    }

    escape(s){ return (s ?? "").toString().replace(/[&<>"']/g, m=>({ "&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;" }[m])); }
}
