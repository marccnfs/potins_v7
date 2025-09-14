import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["canvas"];
    connect(){
        const c = this.canvasTarget;
        const ctx = c.getContext("2d");
        let w, h, raf, t0;

        const PARTICLES = 220;
        const G = 0.15;
        const pieces = [];

        const resize = () => {
            w = c.width = window.innerWidth;
            h = c.height = window.innerHeight;
        };
        window.addEventListener("resize", resize, { passive:true });
        resize();

        const rnd = (a,b) => Math.random()*(b-a)+a;
        const colors = ["#f87171","#34d399","#60a5fa","#fbbf24","#c084fc","#f472b6","#4ade80"];

        for (let i=0;i<PARTICLES;i++){
            pieces.push({
                x: rnd(0,w),
                y: rnd(-h*0.2, -20),
                vx: rnd(-2,2),
                vy: rnd(1,4),
                w: rnd(6,12),
                h: rnd(10,18),
                r: rnd(0,Math.PI),
                vr: rnd(-0.2,0.2),
                c: colors[(Math.random()*colors.length)|0],
                alpha: 1
            });
        }

        const draw = (t) => {
            if (!t0) t0 = t;
            const dt = Math.min(32, t - t0); // ms
            t0 = t;

            ctx.clearRect(0,0,w,h);

            for (const p of pieces){
                p.vy += G * (dt/16);
                p.x += p.vx;
                p.y += p.vy;
                p.r += p.vr;

                // fade out & wrap
                if (p.y > h + 20) { p.y = -20; p.vy = rnd(1,4); }
                if (p.x < -20) p.x = w+20;
                if (p.x > w+20) p.x = -20;

                ctx.save();
                ctx.globalAlpha = p.alpha;
                ctx.translate(p.x, p.y);
                ctx.rotate(p.r);
                ctx.fillStyle = p.c;
                ctx.fillRect(-p.w/2, -p.h/2, p.w, p.h);
                ctx.restore();
            }

            raf = requestAnimationFrame(draw);
        };

        raf = requestAnimationFrame(draw);

        // Stop after 6s (but keep last frame on screen)
        this._timer = setTimeout(() => cancelAnimationFrame(raf), 6000);
        this._cleanup = () => {
            cancelAnimationFrame(raf);
            clearTimeout(this._timer);
            window.removeEventListener("resize", resize);
        };
    }

    disconnect(){ this._cleanup?.(); }
}
