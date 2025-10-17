// assets/controllers/mindar_create_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['model', 'sound', 'mindfile', 'preview'];

    connect() {
        this.previewObjectUrl = null;
    }

    disconnect() {
        this.revokePreviewUrl();
    }

    preview() {
        const container = this.previewTarget;
        container.innerHTML = '';
        this.revokePreviewUrl();
        const model = this.modelTarget.value;
        const sound = this.soundTarget.value;

        const file = this.mindfileTarget.files?.[0];
        if (!file) { alert('Importe un fichier .mind'); return; }
        const url = URL.createObjectURL(file);
        this.previewObjectUrl = url;

        container.insertAdjacentHTML('beforeend', `
<script src="/build/mindar/mindar-image-aframe.prod.js"></script>
<script src="https://aframe.io/releases/1.5.0/aframe.min.js"></script>
<a-scene mindar-image="imageTargetSrc: ${url};" vr-mode-ui="enabled:false" renderer="colorManagement:true">
<a-assets>
<a-asset-item id="model" src="${model}"></a-asset-item>
${sound ? `<audio id="sfx" src="${sound}"></audio>` : ''}
</a-assets>
<a-camera position="0 0 0" look-controls="enabled:false"></a-camera>
<a-entity mindar-image-target="targetIndex: 0">
<a-gltf-model src="#model" scale="0.5 0.5 0.5"
animation__spin="property=rotation; to=0 360 0; loop:true; dur:12000"></a-gltf-model>
${sound ? `<a-entity sound="src:#sfx; autoplay:false; loop:true"></a-entity>` : ''}
</a-entity>
</a-scene>
`);
    }

    async save(){
        const file = this.mindfileTarget.files?.[0];
        if (!file) { alert('Ajoute un .mind pour sauvegarder'); return; }

        const payload = {
            title: 'Scène zen',
            mindTargetPath: '/uploads/mind/' + file.name, // à adapter si tu fais l’upload réel
            targetIndex: 0,
            modelUrl: this.modelTarget.value,
            soundUrl: this.soundTarget.value || null,
        };

        const res = await fetch('/api/ar/scenes', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (res.ok) {
            const data = await res.json();
            alert('Scène sauvegardée (id ' + data.id + ')');
        } else {
            alert('Erreur de sauvegarde');
        }
    }
    revokePreviewUrl() {
        if (this.previewObjectUrl) {
            URL.revokeObjectURL(this.previewObjectUrl);
            this.previewObjectUrl = null;
        }
    }
}
