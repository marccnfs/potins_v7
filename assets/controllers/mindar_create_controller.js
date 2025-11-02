import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['title', 'pack', 'thumbs', 'targetIndex', 'targetInfo', 'model', 'modelChoices', 'modelChoice', 'modelInfo', 'sound', 'mindfile', 'preview', 'packPreview'];
    static values = {
        scenesEndpoint: String,
        uploadEndpoint: String,
    };

    connect() {
        this.previewBlobUrl = null;
        this.selectedPackName = null;

        if (this.hasPackTarget && this.packTarget.options.length > 0) {
            if (this.packTarget.selectedIndex < 0) {
                this.packTarget.selectedIndex = 0;
            }
            this.packChanged();
        } else {
            this._resetThumbs();
        }

        if (this.hasTitleTarget && !this.titleTarget.placeholder) {
            this.titleTarget.placeholder = this._defaultTitle();
        }

        this._initModelSelection();
    }

    disconnect() {
        if (this.previewBlobUrl) {
            URL.revokeObjectURL(this.previewBlobUrl);
            this.previewBlobUrl = null;
        }
    }

    packChanged() {
        if (!this.hasPackTarget) {
            return;
        }

        const option = this.packTarget.selectedOptions?.[0] ?? null;
        const items = option ? this._parseItems(option) : [];
        this.selectedPackName = option?.getAttribute('data-packname')?.trim() || option?.textContent?.trim() || null;
        this._resetPackPreview();
        this._setTargetInfo('Sélectionnez une image pour confirmer la détection MindAR.');
        const hasItems = this._populateThumbs(items);
        if (!hasItems) {
            this._showPackThumbnail(option);
        }
        this._updatePrintLinks(this.selectedPackName);

        if (this.hasTitleTarget && !this.titleTarget.value) {
            this.titleTarget.placeholder = this._defaultTitle();
        }
    }

    preview(event) {
        event?.preventDefault?.();

        const container = this.hasPreviewTarget ? this.previewTarget : document.getElementById('preview');
        if (!container) {
            return;
        }

        container.innerHTML = '';
        if (this.previewBlobUrl) {
            URL.revokeObjectURL(this.previewBlobUrl);
            this.previewBlobUrl = null;
        }

        const asset = this._resolveMindAsset();
        if (asset.type === 'none') {
            alert('Aucun pack MindAR ou fichier .mind sélectionné.');
            return;
        }

        let mindPath = asset.path;
        if (asset.type === 'file') {
            mindPath = URL.createObjectURL(asset.file);
            this.previewBlobUrl = mindPath;
        }

        const idx = this.hasTargetIndexTarget ? parseInt(this.targetIndexTarget.value, 10) || 0 : 0;
        const model = this.hasModelTarget ? this.modelTarget.value : '';
        const sound = this.hasSoundTarget ? this.soundTarget.value : '';
        const audioAsset = sound ? `<audio id="sfx" src="${sound}" crossorigin="anonymous"></audio>` : '';

        container.insertAdjacentHTML('beforeend', `
       <div class="preview-stage">
        <a-scene embedded mindar-image="imageTargetSrc: ${mindPath};" vr-mode-ui="enabled:false" renderer="colorManagement:true" device-orientation-permission-ui="enabled:true" style="width:100%;height:100%;">
          <a-assets>
            <a-asset-item id="mdl" src="${model}"></a-asset-item>
            ${audioAsset}
          </a-assets>
          <a-camera position="0 0 0" look-controls="enabled:false"></a-camera>
          <a-entity mindar-image-target="targetIndex: ${idx}">
            <a-gltf-model src="#mdl" position="0 0 0" scale="0.6 0.6 0.6" animation__spin="property=rotation; to=0 360 0; loop:true; dur:12000"></a-gltf-model>
            ${sound ? `<a-entity sound="src:#sfx; autoplay:false; loop:true"></a-entity>` : ''}
          </a-entity>
        </a-scene>
      </div>
    `);
    }

    async save(event) {
        event?.preventDefault?.();

        const asset = this._resolveMindAsset();
        if (asset.type === 'none') {
            alert('Sélectionne un pack MindAR ou importe un fichier .mind avant d’enregistrer.');
            return;
        }

        let mindTargetPath = asset.path;
        if (asset.type === 'file') {
            try {
                mindTargetPath = await this._uploadMindFile(asset.file);
            } catch (error) {
                console.error(error);
                alert(error.message || 'Erreur lors de l\'upload du fichier .mind.');
                return;
            }
        }

        const payload = {
            title: this._resolveTitle(asset.packName),
            mindTargetPath,
            targetIndex: this.hasTargetIndexTarget ? parseInt(this.targetIndexTarget.value, 10) || 0 : 0,
            modelUrl: this.hasModelTarget ? this.modelTarget.value : '',
            soundUrl: this.hasSoundTarget && this.soundTarget.value ? this.soundTarget.value : null,
        };

        const endpoint = this.hasScenesEndpointValue ? this.scenesEndpointValue : '/api/ar/scenes';

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                const error = await this._extractError(response);
                throw new Error(error || 'Enregistrement impossible.');
            }

            const data = await response.json();
            alert(`Scène sauvegardée (id ${data.id}).`);
        } catch (error) {
            console.error(error);
            alert(error.message || 'Erreur lors de la sauvegarde de la scène.');
        }
    }

    _parseItems(option) {
        try {
            const raw = JSON.parse(option.getAttribute('data-items') || '[]');
            if (Array.isArray(raw)) {
                return raw;
            }
            if (raw && Array.isArray(raw.items)) {
                return raw.items;
            }
            if (raw && Array.isArray(raw.targets)) {
                return raw.targets;
            }
            return [];
        } catch (error) {
            console.warn('Impossible de décoder les items du pack MindAR.', error);
            return [];
        }
    }

    _populateThumbs(items) {
        if (!this.hasThumbsTarget) {
            return false;
        }

        this.thumbsTarget.innerHTML = '';

        const normalized = items
            .map((item, index) => this._normalizeThumbItem(item, index))
            .filter(Boolean);

        if (!normalized.length) {
            if (this.hasTargetIndexTarget) {
                this.targetIndexTarget.value = '0';
            }
            this.thumbsTarget.insertAdjacentHTML('beforeend', '<p class="text-sm text-gray-500">Aucune miniature pour ce pack.</p>');
            this._resetPackPreview();
            this._setTargetInfo('Sélectionnez une image pour confirmer la détection MindAR.');
            return false;
        }

        normalized.forEach((item) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'target-thumb border rounded p-1 hover:ring focus:ring';
            btn.dataset.targetIndex = `${item.index ?? 0}`;
            btn.setAttribute('aria-pressed', 'false');
            const indexValue = Number.isFinite(item.index) ? item.index : Number.parseInt(item.index ?? '0', 10);
            const displayIndex = Number.isFinite(indexValue) ? indexValue + 1 : 1;
            btn.setAttribute('aria-label', `Cible ${displayIndex}${item.label ? ` – ${item.label}` : ''}`);
            btn.innerHTML = `
                <img src="${item.thumb}" alt="${item.label ?? ''}" class="w-full h-auto" />
                <div class="text-xs text-center">${item.label ?? 'Cible'}</div>
            `;
            btn.addEventListener('click', () => {
                if (this.hasTargetIndexTarget) {
                    this.targetIndexTarget.value = item.index ?? 0;
                }
                this._highlight(btn);
                this._showSelectedThumb(item);
            });
            this.thumbsTarget.appendChild(btn);
        });

        const firstBtn = this.thumbsTarget.querySelector('button');
        if (firstBtn) {
            firstBtn.click();
        }
        return true;
    }

    _resetThumbs() {
        if (!this.hasThumbsTarget) {
            return;
        }

        this.thumbsTarget.innerHTML = '<p class="text-sm text-gray-500">Aucun pack MindAR détecté pour le moment.</p>';
        if (this.hasTargetIndexTarget) {
            this.targetIndexTarget.value = '0';
        }
        this._resetPackPreview();
        this._setTargetInfo('Sélectionnez une image pour confirmer la détection MindAR.');
    }

    _highlight(active) {
        if (!this.hasThumbsTarget) {
            return;
        }

        this.thumbsTarget.querySelectorAll('button').forEach((button) => {
            button.classList.remove('ring', 'ring-blue-500');
            button.setAttribute('aria-pressed', 'false');
        });
        active.classList.add('ring', 'ring-blue-500');
        active.setAttribute('aria-pressed', 'true');
    }

    _normalizeThumbItem(item, fallbackIndex) {
        if (!item || typeof item !== 'object') {
            return null;
        }

        const candidate = Number.parseInt(item.index, 10);
        const index = Number.isNaN(candidate) ? fallbackIndex : candidate;
        const thumb = item.thumb || item.thumbnail || item.image || item.url || null;
        const image = item.image || item.source || thumb;
        if (!thumb && !image) {
            return null;
        }

        return {
            index,
            label: item.label || item.name || item.id || `Cible ${index + 1}`,
            thumb: thumb || image,
            image: image,
        };
    }

    _showSelectedThumb(item) {
        if (!this.hasPackPreviewTarget) {
            return;
        }

        if (!item) {
            this._resetPackPreview();
            this._setTargetInfo('Sélectionnez une image pour confirmer la détection MindAR.');
            return;
        }

        const source = item.image || item.thumb;
        if (!source) {
            this._resetPackPreview();
            this._setTargetInfo('Sélectionnez une image pour confirmer la détection MindAR.');
            return;
        }

        const label = item.label || '';
        const indexValue = Number.isFinite(item.index) ? item.index : Number.parseInt(item.index ?? '0', 10);
        const index = Number.isFinite(indexValue) ? indexValue : 0;
        this.packPreviewTarget.innerHTML = `
            <figure class="pack-preview">
                <img src="${source}" alt="${label}" class="pack-preview__image" />
                ${label ? `<figcaption class="pack-preview__caption">${label}</figcaption>` : ''}
            </figure>
        `;
        this._setTargetInfo(`Image détectée : ${label || 'Cible'} (index MindAR ${index})`);
    }

    _showPackThumbnail(option) {
        if (!this.hasPackPreviewTarget) {
            return;
        }

        const thumbnail = option?.getAttribute('data-thumbnail');
        if (thumbnail) {
            this.packPreviewTarget.innerHTML = `
                <figure class="pack-preview">
                    <img src="${thumbnail}" alt="${this.selectedPackName ?? ''}" class="pack-preview__image" />
                    ${this.selectedPackName ? `<figcaption class="pack-preview__caption">${this.selectedPackName}</figcaption>` : ''}
                </figure>
            `;
            this._setTargetInfo(`Motif par défaut sélectionné pour ${this.selectedPackName ?? 'ce pack'}.`);
        } else {
            this._resetPackPreview();
        }
    }

    _resetPackPreview() {
        if (!this.hasPackPreviewTarget) {
            return;
        }

        this.packPreviewTarget.innerHTML = '<p class="text-sm text-gray-500">Sélectionnez un pack pour afficher un aperçu du motif.</p>';
        this._setTargetInfo('Sélectionnez une image pour confirmer la détection MindAR.');
    }

    selectModel(event) {
        event?.preventDefault?.();
        const button = event?.currentTarget;
        if (!button) {
            return;
        }

        this._selectModelButton(button);
    }

    _initModelSelection() {
        if (!this.hasModelTarget || !this.hasModelChoiceTarget) {
            return;
        }

        const currentValue = this.modelTarget.value?.trim();
        let candidate = null;

        if (currentValue) {
            candidate = this.modelChoiceTargets.find((button) => button.getAttribute('data-model-path') === currentValue) ?? null;
        }

        if (!candidate) {
            candidate = this.modelChoiceTargets.find((button) => button.hasAttribute('data-model-default')) ?? null;
        }

        if (!candidate) {
            candidate = this.modelChoiceTargets[0] ?? null;
        }

        if (candidate) {
            this._selectModelButton(candidate, { focus: false });
        } else {
            this._updateModelInfo();
        }
    }

    _selectModelButton(button, options = { focus: true }) {
        if (!button || !this.hasModelTarget) {
            return;
        }

        const path = button.getAttribute('data-model-path');
        if (path) {
            this.modelTarget.value = path;
        }

        this._highlightModel(button);
        this._updateModelInfo(button);

        if (options.focus) {
            button.focus?.();
        }
    }

    _highlightModel(active) {
        if (!this.hasModelChoiceTarget) {
            return;
        }

        this.modelChoiceTargets.forEach((button) => {
            button.classList.remove('model-card--active');
            button.setAttribute('aria-pressed', 'false');
        });

        active.classList.add('model-card--active');
        active.setAttribute('aria-pressed', 'true');
    }

    _updateModelInfo(button = null) {
        if (!this.hasModelInfoTarget) {
            return;
        }

        if (!button) {
            this.modelInfoTarget.innerHTML = '<p class="text-xs text-gray-500">Sélectionnez un modèle pour afficher ses détails.</p>';
            return;
        }

        const name = button.getAttribute('data-model-name') || 'Modèle 3D';
        const description = button.getAttribute('data-model-description') || '';
        const emoji = button.getAttribute('data-model-emoji') || '🧊';

        this.modelInfoTarget.innerHTML = `
            <div class="model-info__content">
                <span class="model-info__emoji" aria-hidden="true">${emoji}</span>
                <div>
                    <p class="model-info__title">${name}</p>
                    ${description ? `<p class="model-info__description">${description}</p>` : ''}
                </div>
            </div>
        `;
    }

    _setTargetInfo(message) {
        if (!this.hasTargetInfoTarget) {
            return;
        }

        this.targetInfoTarget.textContent = message;
    }


    _resolveMindAsset() {
        const file = this.hasMindfileTarget ? this.mindfileTarget.files?.[0] ?? null : null;
        if (file) {
            return { type: 'file', file, packName: this.selectedPackName, path: null };
        }

        if (this.hasPackTarget) {
            const option = this.packTarget.selectedOptions?.[0];
            if (option?.value) {
                const packName = option.getAttribute('data-packname')?.trim() || option.textContent?.trim() || null;
                const path = this._normalizeMindPath(option.value);
                return { type: 'pack', path, packName };
            }
        }

        return { type: 'none', path: null, packName: null };
    }

    _normalizeMindPath(path) {
        if (!path) {
            return path;
        }

        if (path.startsWith('file:///')) {
            try {
                const url = new URL(path);
                let normalizedPath = url.pathname.replace(/\\/g, '/');
                const idx = normalizedPath.toLowerCase().indexOf('/public/');
                if (idx !== -1) {
                    const relative = normalizedPath.substring(idx + '/public'.length);
                    return relative.startsWith('/') ? relative : `/${relative}`;
                }
                return normalizedPath.startsWith('/') ? normalizedPath : `/${normalizedPath}`;
            } catch (error) {
                console.warn('Impossible de normaliser le chemin MindAR', error);
                const fallback = path.replace(/^file:\/\//, '').replace(/\\/g, '/');
                const parts = fallback.split('/public/');
                if (parts.length > 1) {
                    const relative = parts[1];
                    return relative.startsWith('/') ? relative : `/${relative}`;
                }
                return fallback.startsWith('/') ? fallback : `/${fallback}`;
            }
        }

        return path;
    }

    async _uploadMindFile(file) {
        const endpoint = this.hasUploadEndpointValue ? this.uploadEndpointValue : '/api/upload/mind';
        const formData = new FormData();
        formData.append('file', file, file.name || `targets_${Date.now()}.mind`);

        const response = await fetch(endpoint, { method: 'POST', body: formData, credentials: 'same-origin' });
        if (!response.ok) {
            const error = await this._extractError(response);
            throw new Error(error || 'Upload du fichier .mind impossible.');
        }

        const data = await response.json();
        if (!data?.path) {
            throw new Error('Réponse inattendue de l\'upload MindAR.');
        }

        return data.path;
    }

    async _extractError(response) {
        try {
            const data = await response.json();
            if (data?.error) {
                return data.error;
            }
            if (data?.message) {
                return data.message;
            }
        } catch (error) {
            console.warn('Impossible de lire la réponse JSON de l\'API.', error);
        }
        return null;
    }

    _resolveTitle(packName) {
        const explicitTitle = this.hasTitleTarget ? this.titleTarget.value.trim() : '';
        if (explicitTitle) {
            return explicitTitle;
        }

        if (packName) {
            return `Scène ${packName}`;
        }

        return 'Scène MindAR';
    }

    _defaultTitle() {
        return this.selectedPackName ? `Scène ${this.selectedPackName}` : 'Ma scène RA';
    }

    _updatePrintLinks(packName) {
        const one = document.getElementById('btn-print-one');
        const sheet = document.getElementById('btn-print-sheet');

        if (!packName) {
            if (one) one.removeAttribute('href');
            if (sheet) sheet.removeAttribute('href');
            return;
        }

        if (one) {
            one.href = `/ra/markers/print/${encodeURIComponent(packName)}`;
        }
        if (sheet) {
            sheet.href = `/ra/markers/sheet/${encodeURIComponent(packName)}`;
        }
    }
}
