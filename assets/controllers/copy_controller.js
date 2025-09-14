import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    run(event){
        const card = event.currentTarget.closest('.step-section');
        const pre = card?.querySelector('[data-copy]');
        if (!pre) return;
        const txt = pre.textContent.trim();
        navigator.clipboard.writeText(txt).then(()=>{
            event.currentTarget.textContent = "Copié ✓";
            setTimeout(()=> event.currentTarget.textContent = "Copier", 1200);
        });
    }
}
