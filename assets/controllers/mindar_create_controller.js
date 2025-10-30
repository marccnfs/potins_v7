import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['title', 'pack', 'thumbs', 'targetIndex', 'model', 'sound', 'mindfile', 'preview'];
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
        this._populateThumbs(items);
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
      <a-scene mindar-image="imageTargetSrc: ${mindPath};" vr-mode-ui="enabled:false" renderer="colorManagement:true" device-orientation-permission-ui="enabled:true">
        <a-assets>
          <a-asset-item id="mdl" src="${model}"></a-asset-item>
           ${audioAsset}
        </a-assets>
        <a-camera position="0 0 0" look-controls="enabled:false"></a-camera>
        <a-entity mindar-image-target="targetIndex: ${idx}">
          <a-gltf-model src="#mdl" scale="0.3 0.3 0.3" animation__spin="property=rotation; to=0 360 0; loop:true; dur:12000"></a-gltf-model>
          ${sound ? `<a-entity sound="src:#sfx; autoplay:false; loop:true"></a-entity>` : ''}
        </a-entity>
      </a-scene>
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
            return JSON.parse(option.getAttribute('data-items') || '[]') || [];
        } catch (error) {
            console.warn('Impossible de décoder les items du pack MindAR.', error);
            return [];
        }
    }

    _populateThumbs(items) {
        if (!this.hasThumbsTarget) {
            return;
        }

        this.thumbsTarget.innerHTML = '';

        if (!items.length) {
            if (this.hasTargetIndexTarget) {
                this.targetIndexTarget.value = '0';
            }
            this.thumbsTarget.insertAdjacentHTML('beforeend', '<p class="text-sm text-gray-500">Aucune miniature pour ce pack.</p>');
            return;
        }

        items.forEach((item) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'border rounded p-1 hover:ring focus:ring';
            btn.innerHTML = `
                <img src="${item.thumb}" alt="${item.label ?? ''}" class="w-full h-auto" />
                <div class="text-xs text-center">${item.label ?? 'Cible'}</div>
            `;
            btn.addEventListener('click', () => {
                if (this.hasTargetIndexTarget) {
                    this.targetIndexTarget.value = item.index ?? 0;
                }
                this._highlight(btn);
            });
            this.thumbsTarget.appendChild(btn);
        });

        const firstBtn = this.thumbsTarget.querySelector('button');
        if (firstBtn) {
            firstBtn.click();
        }
    }

    _resetThumbs() {
        if (!this.hasThumbsTarget) {
            return;
        }

        this.thumbsTarget.innerHTML = '<p class="text-sm text-gray-500">Aucun pack MindAR détecté pour le moment.</p>';
        if (this.hasTargetIndexTarget) {
            this.targetIndexTarget.value = '0';
        }
    }

    _highlight(active) {
        if (!this.hasThumbsTarget) {
            return;
        }

        this.thumbsTarget.querySelectorAll('button').forEach((button) => {
            button.classList.remove('ring', 'ring-blue-500');
        });
        active.classList.add('ring', 'ring-blue-500');
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
