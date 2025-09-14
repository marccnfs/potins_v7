import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["duration"];

    connect(){
        // Option : lire un timer stocké côté joueur, ex: localStorage key `eg:{slug}:totalMs`
        // Tu peux enregistrer ce total à la fin de l’étape 6.
        const url = new URL(window.location.href);
        const slug = url.pathname.split("/").filter(Boolean)[1] || "";
        const key = `eg:${slug}:totalMs`;
        const ms = parseInt(localStorage.getItem(key) || "0", 10);
        if (this.hasDurationTarget) {
            this.durationTarget.textContent = ms > 0 ? this.format(ms) : "—";
        }
    }

    copy(){
        const shareUrl = window.location.origin + window.location.pathname.replace(/\/the-end.*/, "");
        navigator.clipboard.writeText(shareUrl).then(()=>{
            this.flash("Lien copié !");
        });
    }

    flash(text){
        // mini-toast inline
        const div = document.createElement("div");
        div.textContent = text;
        div.style.cssText = "position:fixed;right:1rem;bottom:1rem;background:#111;color:#fff;padding:.5rem .75rem;border-radius:.6rem;z-index:60;box-shadow:0 10px 30px rgba(0,0,0,.35)";
        document.body.appendChild(div);
        setTimeout(()=>div.remove(), 2000);
    }

    format(ms){
        const s = Math.round(ms/1000);
        const m = Math.floor(s/60);
        const r = s%60;
        return `${m} min ${r.toString().padStart(2,'0')} s`;
    }
}
