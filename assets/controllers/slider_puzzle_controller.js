// assets/controllers/slider_puzzle_controller.js
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = {
        image: String,
        rows: { type: Number, default: 3 },
        cols: { type: Number, default: 3 },
        okMessage: { type: String, default: "Parfait !" }
    }
    static targets = ["grid","msg"]

    initialize(){
        this._onDragStart = this.onDragStart.bind(this);
        this._onDragOver  = this.onDragOver.bind(this);
        this._onDrop      = this.onDrop.bind(this);
    }

    connect(){
        // borne minimale
        this.r = Math.max(2, this.rowsValue|0);
        this.c = Math.max(2, this.colsValue|0);
        this.total = this.r * this.c;

        // index “corrects”
        this.tiles = Array.from({length:this.total}, (_,i)=>i);
        // ordre courant (mélangé)
        this.order = this.shuffle(this.tiles.slice());

        this.render();
    }

    disconnect(){
        // nettoyage listeners si besoin
        if (this.hasGridTarget) {
            this.gridTarget.querySelectorAll(".slider-tile").forEach(el=>{
                el.removeEventListener("dragstart", this._onDragStart);
                el.removeEventListener("dragover", this._onDragOver);
                el.removeEventListener("drop", this._onDrop);
            });
        }
    }

    shuffle(arr){
        // Fisher–Yates puis s’assurer que l’état n’est pas déjà résolu
        if (arr.length <= 1) return arr;
        do {
            for (let i = arr.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [arr[i], arr[j]] = [arr[j], arr[i]];
            }
        } while (arr.every((v,i)=>v===i));
        return arr;
    }

    render(){
        const g = this.gridTarget;
        g.innerHTML = "";
        g.style.setProperty("--rows", this.r);
        g.style.setProperty("--cols", this.c);
        g.style.setProperty("--img-url", `url("${this.imageValue}")`);

        this.order.forEach((tileIdx, posIdx)=>{
            const r = Math.floor(tileIdx / this.c);
            const c = tileIdx % this.c;

            const cell = document.createElement("div");
            cell.className = "slider-tile";
            cell.draggable = true;
            // position courante (dans la grille)
            cell.dataset.pos = String(posIdx);
            // index de la tuile (son “morceau” théorique)
            cell.dataset.tile = String(tileIdx);

            // variables CSS pour découper correctement
            cell.style.setProperty("--r", r);
            cell.style.setProperty("--c", c);

            cell.addEventListener("dragstart", this._onDragStart);
            cell.addEventListener("dragover", this._onDragOver);
            cell.addEventListener("drop", this._onDrop);

            g.appendChild(cell);
        });

        this.check();
    }

    onDragStart(e){
        e.dataTransfer.setData("text/plain", e.currentTarget.dataset.pos);
    }

    onDragOver(e){
        e.preventDefault();
    }

    onDrop(e){
        e.preventDefault();
        const from = parseInt(e.dataTransfer.getData("text/plain"), 10);
        const to   = parseInt(e.currentTarget.dataset.pos, 10);
        if (Number.isNaN(from) || Number.isNaN(to) || from === to) return;
        [this.order[from], this.order[to]] = [this.order[to], this.order[from]];
        this.render(); // re-render met à jour data-pos et les listeners proprement
    }

    check(){
        const ok = this.order.every((v,i)=>v===i);
        if (this.hasMsgTarget) this.msgTarget.textContent = ok ? `✅ ${this.okMessageValue}` : "";
        if (ok) document.dispatchEvent(new CustomEvent("puzzle:solved",{bubbles:true}));
    }
}
