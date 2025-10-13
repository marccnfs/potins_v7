// assets/controllers/ar/mindar_build_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['images','score','uploadBtn'];

  connect(){
    this.mindBlob = null;   // Blob du .mind prêt
    this.targetCount = 0;   // nb d’images dans le .mind
  }

  async compile(){
    const files = Array.from(this.imagesTarget.files || []);
    if (files.length === 0) { alert('Ajoute au moins une image'); return; }

    // Charger dynamiquement le compilateur (exposé en global par le script)
    if (!window.MINDAR) { alert('MindAR non chargé'); return; }
    const compiler = new window.MINDAR.IMAGE.Compiler(); // objet compilateur

    // Astuce : MindAR attend des ImageBitmap/HTMLImageElement
    const bitmaps = [];
    for (const f of files) {
      const imgUrl = URL.createObjectURL(f);
      const img = await this._loadImage(imgUrl);
      const bitmap = await createImageBitmap(img);
      bitmaps.push(bitmap);
    }

    // Compile : tu peux passer un tableau pour packer plusieurs cibles
    const result = await compiler.compileImages(bitmaps);

    // result possède les données binaires + meta (feature scores par image)
    const { mindData /* Uint8Array */, images: metas /* scores */ } = result;

    // Feedback qualité
    const lines = metas.map((meta, i) => {
      const score = Math.round((meta.featurePoints || 0));  // selon version : meta.featuresCount/featurePoints
      return `Image ${i} → points: ${score}`;
    }).join('<br>');
    this.scoreTarget.innerHTML = `Qualité détectée :<br>${lines}`;

    // Construit un Blob et mémorise
    this.mindBlob = new Blob([mindData], { type: 'application/octet-stream' });
    this.targetCount = files.length;
    this.uploadBtnTarget.disabled = false;
    alert('Compilation OK : prévisualise maintenant sur /ra/mindar/create ou téléverse.');
  }

  async upload(){
    if (!this.mindBlob) { alert('Compile d’abord'); return; }

    const form = new FormData();
    form.append('file', this.mindBlob, `targets_${Date.now()}.mind`);

    const res = await fetch('/api/upload/mind', { method: 'POST', body: form });
    if (!res.ok) { alert('Upload KO'); return; }

    const data = await res.json(); // { path: '/uploads/mind/targets_123.mind' }
    alert('Fichier envoyé : ' + data.path);
  }

  _loadImage(url){
    return new Promise((resolve, reject) => {
      const img = new Image();
      img.onload = () => resolve(img);
      img.onerror = reject;
      img.src = url;
    });
  }
}
