// assets/controllers/ar_create_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['model', 'marker'];

    apply() {
        const modelUrl = this.modelTarget.value;
        const markerPreset = this.markerTarget.value;

        const modelEl = document.querySelector('#model-dyn');
        const markerEl = document.querySelector('#marker-dyn');

        if (modelEl) modelEl.setAttribute('gltf-model', `url(${modelUrl})`);
        if (markerEl) markerEl.setAttribute('preset', markerPreset);
    }
}
