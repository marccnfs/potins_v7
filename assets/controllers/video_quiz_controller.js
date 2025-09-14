// assets/controllers/video_quiz_controller.js
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = {
        src: String,
        cues: Array,
        okMessage: { type: String, default: "Toutes les questions validées !" }
    }
    static targets = ["video","overlay","msg"]

    initialize(){
        // Binding unique, réutilisé entre connect/disconnect
        this._onTime = this.onTime.bind(this);
        this.asked = new Set();
    }

    connect(){
        // Si on a la valeur "src" côté data-*, synchroniser la <video> si vide
        if (this.hasVideoTarget) {
            if (this.srcValue && !this.videoTarget.currentSrc) {
                // Cas où le <source> n’est pas présent dans le DOM d’aperçu
                this.videoTarget.src = this.srcValue;
            }
            // Éviter double abonnement
            this.videoTarget.removeEventListener("timeupdate", this._onTime);
            this.videoTarget.addEventListener("timeupdate", this._onTime);
        }
    }

    disconnect(){
        if (this.hasVideoTarget) {
            this.videoTarget.removeEventListener("timeupdate", this._onTime);
        }
    }

    onTime(){
        if (!this.hasVideoTarget) return;
        const t = this.videoTarget.currentTime;
        // Trouver la 1ère cue non traitée dont le temps est dépassé
        const i = (this.cuesValue || []).findIndex((c,idx)=> t>=Number(c.time||0) && !this.asked.has(idx));
        if (i >= 0) {
            this.videoTarget.pause();
            this.ask(i, this.cuesValue[i]);
        }
    }

    ask(idx, cue){
        if (!this.hasOverlayTarget || !this.hasMsgTarget) return;

        this.overlayTarget.innerHTML = "";
        this.overlayTarget.hidden = false;

        const card = document.createElement("div");
        card.className = "vq-card";
        const q = document.createElement("p"); q.textContent = cue.question || "Question";
        card.appendChild(q);

        (cue.options || []).forEach(opt=>{
            const btn = document.createElement("button");
            btn.type = "button";
            btn.textContent = opt.label;
            btn.addEventListener("click", ()=>{
                if (opt.id === cue.answer){
                    this.msgTarget.textContent = cue.feedbackOk || "OK";
                    this.overlayTarget.hidden = true;
                    this.asked.add(idx);
                    if (this.asked.size === (this.cuesValue || []).length){
                        this.msgTarget.textContent = "✅ " + this.okMessageValue;
                        document.dispatchEvent(new CustomEvent("puzzle:solved",{bubbles:true}));
                    }
                    this.videoTarget?.play();
                } else {
                    this.msgTarget.textContent = cue.feedbackKo || "Non…";
                }
            });
            card.appendChild(btn);
        });

        this.overlayTarget.appendChild(card);
    }
}
