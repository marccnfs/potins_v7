import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['pack','thumbs','targetIndex','model'];

    connect(){ this.packChanged(); }

    packChanged(){
        const opt = this.packTarget.selectedOptions[0];
        const items = JSON.parse(opt.getAttribute('data-items') || '[]');
        const packName = opt.getAttribute('data-packname') || 'pack';

        this.thumbsTarget.innerHTML = '';
        items.forEach(item => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'border rounded p-1 hover:ring';
            btn.innerHTML = `<img src="${item.thumb}" alt="${item.label}" class="w-full h-auto"/><div class="text-xs text-center">${item.label}</div>`;
            btn.addEventListener('click', () => { this.targetIndexTarget.value = item.index; this._highlight(btn); });
            this.thumbsTarget.appendChild(btn);
        });
        const firstBtn = this.thumbsTarget.querySelector('button');
        if (firstBtn) firstBtn.click();

        const one = document.getElementById('btn-print-one');
        const sheet = document.getElementById('btn-print-sheet');
        if (one) one.href  = `/ra/markers/print/${encodeURIComponent(packName)}`;
        if (sheet) sheet.href = `/ra/markers/sheet/${encodeURIComponent(packName)}`;
    }

    _highlight(active){
        this.thumbsTarget.querySelectorAll('button').forEach(b => b.classList.remove('ring','ring-blue-500'));
        active.classList.add('ring','ring-blue-500');
    }

    preview(){
        const container = document.getElementById('preview');
        container.innerHTML = '';

        //const mindPath = this.packTarget.value;
        const idx = parseInt(this.targetIndexTarget.value, 10);


        const model = this.modelTarget.value;
        const sound = this.soundTarget.value;

        // 1️⃣ Détermination du chemin MindAR
        let mindPath = null;

        const file = this.mindfileTarget.files?.[0];
        if (file) {
            // L’utilisateur a importé un .mind → on l’upload ou on le sert via un blob temporaire
            mindPath = URL.createObjectURL(file);
        } else if (this.packTarget && this.packTarget.value) {
            // Sélecteur de pack → déjà hébergé dans /public/mindar/packs/
            mindPath = this.packTarget.value;

            // sécurité : si le chemin commence par "file://", on le corrige
            if (mindPath.startsWith('file:///')) {
                mindPath = mindPath.replace(/^file:\/\/\/[A-Za-z]:[\\/]+potins_v7[\\/]+public/, '');
            }
        }

        if (!mindPath) {
            alert('Aucun pack MindAR ou fichier .mind sélectionné.');
            return;
        }

        container.insertAdjacentHTML('beforeend', `
      <a-scene mindar-image="imageTargetSrc: ${mindPath};" vr-mode-ui="enabled:false" renderer="colorManagement:true"  device-orientation-permission-ui="enabled:true">
        <a-assets>
          <a-asset-item id="mdl" src="${model}"></a-asset-item>
        </a-assets>
        <a-camera position="0 0 0" look-controls="enabled:false"></a-camera>
        <a-entity mindar-image-target="targetIndex: ${idx}">
          <a-gltf-model src="#mdl" scale="0.3 0.3 0.3"
            animation__spin="property=rotation; to=0 360 0; loop:true; dur:12000"></a-gltf-model>
            ${sound ? `<a-entity sound="src:#sfx; autoplay:false; loop:true"></a-entity>` : ''}
        </a-entity>
      </a-scene>
    `);
    }
}
