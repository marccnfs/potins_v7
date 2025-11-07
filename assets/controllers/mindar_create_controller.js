import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'title',
        'pack',
        'thumbs',
        'targetIndex',
        'targetInfo',
        'asset',
        'assetType',
        'modelSection',
        'modelChoices',
        'modelChoice',
        'modelInfo',
        'videoSection',
        'videoInput',
        'imageSection',
        'imageInput',
        'sound',
        'mindfile',
        'preview',
        'packPreview',
        'positionX',
        'positionY',
        'positionZ',
        'rotationX',
        'rotationY',
        'rotationZ',
        'scaleX',
        'scaleY',
        'scaleZ',
        'sharePanel',
        'shareLink',
        'experienceLink',
        'shareQr',
        'status',
        'form',
    ];

    static values = {
        scenesEndpoint: String,
        uploadEndpoint: String,
        cancelUrl: String,
        mode: String,
        updateEndpoint: String,
        initialScene: Object,
    };

    connect() {
        this.previewBlobUrl = null;
        this.selectedPackName = null;
        this.persistedMindTargetPath = null;
        this.thumbItems = [];
        this.persistedMindTargetPath = null;
        this.thumbItems = [];
        this.previewBackground = null;
        this.isSaving = false;
        this._clearStatus();

        this.defaultModelLibrary = this._snapshotModelLibrary();

        if (this.hasPackTarget) {
            if (this.packTarget.value) {
                this.packChanged();
            } else {
                this._resetThumbs();
            }
        } else {
            this._resetThumbs();
        }

        if (this.hasTitleTarget && !this.titleTarget.placeholder) {
            this.titleTarget.placeholder = this._defaultTitle();
        }

        this._toggleAssetSections(this._currentAssetType());
        this._initModelSelection();
        this.renderPlacementPreview();

        if (this._isEditMode() && this.hasInitialSceneValue) {
            this._applyInitialScene(this.initialSceneValue);
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
        if (!option || !option.value) {
            this.selectedPackName = null;
            this.persistedMindTargetPath = null;
            this._resetThumbs();
            this._resetPackPreview();
            this._updatePrintLinks(null);
            this._updateModelLibrary([]);
            return;
        }
        const items = option ? this._parseItems(option) : [];
        const models = option ? this._parseModels(option) : [];
        this.selectedPackName = option?.getAttribute('data-packname')?.trim() || option?.textContent?.trim() || null;
        this.persistedMindTargetPath = this._normalizeMindPath(option.value);
        this._resetPackPreview();
        this._setTargetInfo('Sélectionnez une image pour confirmer la détection MindAR.');
        this._populateThumbs(items);
        this._showPackThumbnail(option);
        this._updatePrintLinks(this.selectedPackName);
        this._updateModelLibrary(models);

        if (this.hasTitleTarget && !this.titleTarget.value) {
            this.titleTarget.placeholder = this._defaultTitle();
        }
    }

    _applyInitialScene(scene) {
        if (!scene || typeof scene !== 'object') {
            return;
        }

        const contentType = scene.contentType || 'model';
        this.persistedMindTargetPath = scene.mindTargetPath || null;

        if (this.hasTitleTarget && typeof scene.title === 'string') {
            this.titleTarget.value = scene.title;
        }

        if (this.hasAssetTarget) {
            this.assetTarget.value = scene.assetUrl || scene.modelUrl || '';
        }

        this._setAssetType(contentType);

        if (contentType === 'video' && this.hasVideoInputTarget) {
            this.videoInputTarget.value = scene.assetUrl || scene.modelUrl || '';
        } else if (contentType === 'image' && this.hasImageInputTarget) {
            this.imageInputTarget.value = scene.assetUrl || scene.modelUrl || '';
        }

        if (this.hasSoundTarget) {
            this.soundTarget.value = scene.soundUrl || '';
        }

        const transform = scene.transform || {};
        this._applyVectorValue('position', transform.position || {});
        this._applyVectorValue('rotation', transform.rotation || {});
        this._applyVectorValue('scale', transform.scale || {});

        if (this.hasPackTarget && scene.mindTargetPath) {
            this._selectPackOptionByPath(scene.mindTargetPath, scene.packName || null);
        }

        if (this.hasTargetIndexTarget && scene.targetIndex !== undefined && scene.targetIndex !== null) {
            this.targetIndexTarget.value = scene.targetIndex;
            this._highlightTargetByIndex(Number.parseInt(scene.targetIndex, 10));
        }

        this.renderPlacementPreview();
    }

    _applyVectorValue(kind, values) {
        if (!values || typeof values !== 'object') {
            return;
        }

        if (kind === 'position') {
            if (this.hasPositionXTarget && values.x !== undefined) this.positionXTarget.value = values.x;
            if (this.hasPositionYTarget && values.y !== undefined) this.positionYTarget.value = values.y;
            if (this.hasPositionZTarget && values.z !== undefined) this.positionZTarget.value = values.z;
        } else if (kind === 'rotation') {
            if (this.hasRotationXTarget && values.x !== undefined) this.rotationXTarget.value = values.x;
            if (this.hasRotationYTarget && values.y !== undefined) this.rotationYTarget.value = values.y;
            if (this.hasRotationZTarget && values.z !== undefined) this.rotationZTarget.value = values.z;
        } else if (kind === 'scale') {
            if (this.hasScaleXTarget && values.x !== undefined) this.scaleXTarget.value = values.x;
            if (this.hasScaleYTarget && values.y !== undefined) this.scaleYTarget.value = values.y;
            if (this.hasScaleZTarget && values.z !== undefined) this.scaleZTarget.value = values.z;
        }
    }

    _selectPackOptionByPath(path, packName = null) {
        if (!this.hasPackTarget || !path) {
            return false;
        }

        const normalized = this._normalizeMindPath(path);
        const options = Array.from(this.packTarget.options ?? []);
        let match = options.find((option) => this._normalizeMindPath(option.value) === normalized) ?? null;

        if (!match) {
            match = document.createElement('option');
            match.value = normalized;
            match.textContent = packName || 'Pack existant';
            match.setAttribute('data-packname', packName || 'Pack existant');
            match.selected = true;
            this.packTarget.add(match);
        } else {
            match.selected = true;
        }

        this.persistedMindTargetPath = normalized;
        this.packChanged();
        return true;
    }

    _highlightTargetByIndex(index) {
        if (!this.hasThumbsTarget || index === undefined || index === null) {
            return;
        }

        const numericIndex = Number.parseInt(index, 10);
        if (Number.isNaN(numericIndex)) {
            return;
        }

        const button = this._findThumbButtonByIndex(numericIndex);
        if (!button) {
            return;
        }

        if (this.hasTargetIndexTarget) {
            this.targetIndexTarget.value = numericIndex;
        }

        this._highlight(button);
        const item = this._resolveThumbItem(button, numericIndex);
        this._showSelectedThumb(item);
    }

    _findThumbButtonByIndex(index) {
        if (!this.hasThumbsTarget) {
            return null;
        }

        const buttons = Array.from(this.thumbsTarget.querySelectorAll('button[data-target-index]'));
        return buttons.find((button) => Number.parseInt(button.dataset.targetIndex ?? '', 10) === index) ?? null;
    }

    _resolveThumbItem(button, index) {
        if (!button) {
            return null;
        }

        const label = button.getAttribute('data-thumb-label') || '';
        const thumb = button.getAttribute('data-thumb-thumb') || '';
        const image = button.getAttribute('data-thumb-image') || thumb;

        return {
            index,
            label,
            thumb: thumb || image,
            image,
        };
    }

    _currentMode() {
        return this.hasModeValue ? this.modeValue : 'create';
    }

    _isEditMode() {
        return this._currentMode() === 'edit';
    }

    preview(event) {
        event?.preventDefault?.();

        if (!this._ensureAssetSelected()) {
            return;
        }

        this.renderPlacementPreview();
    }

    renderPlacementPreview() {
        if (!this.hasPreviewTarget) {
            return;
        }

        const container = this.previewTarget;
        const assetType = this._currentAssetType();
        const assetUrl = this._currentAssetUrl();

        if (!assetUrl) {
            this._renderPreviewPlaceholder();
            return;
        }

        const position = this._formatVector(this._collectVector('position'));
        const rotation = this._formatVector(this._collectVector('rotation'));
        const scale = this._formatVector(this._collectVector('scale'));

        container.innerHTML = this._buildPlacementPreview(assetType, assetUrl, position, rotation, scale);
        this._applyPreviewBackground();
    }

    async save(event) {
        event?.preventDefault?.();

        if (this.isSaving) {
            return;
        }

        const asset = this._resolveMindAsset();
        if (asset.type === 'none') {
            alert('Sélectionne un pack MindAR ou importe un fichier .mind avant d’enregistrer.');
            return;
        }

        if (!this._ensureAssetSelected()) {
            return;
        }

        this._clearStatus();

        const trigger = event?.currentTarget instanceof HTMLElement ? event.currentTarget : null;
        const originalLabel = trigger?.textContent;
        if (trigger) {
            trigger.disabled = true;
            trigger.classList.add('is-busy');
            trigger.textContent = 'Sauvegarde…';
        }

        this.isSaving = true;


        let mindTargetPath = asset.path;
        if (asset.type === 'file') {
            try {
                mindTargetPath = await this._uploadMindFile(asset.file);
            } catch (error) {
                console.error(error);
                this._setStatus(error.message || "Erreur lors de l'upload du fichier .mind.", 'error');
                this._unlockTrigger(trigger, originalLabel);
                this.isSaving = false;
                return;
            }
        }

        if (mindTargetPath) {
            this.persistedMindTargetPath = mindTargetPath;
        }

        const payload = {
            title: this._resolveTitle(asset.packName),
            mindTargetPath,
            targetIndex: this.hasTargetIndexTarget ? parseInt(this.targetIndexTarget.value, 10) || 0 : 0,
            assetUrl: this._currentAssetUrl(),
            contentType: this._currentAssetType(),
            soundUrl: this.hasSoundTarget && this.soundTarget.value ? this.soundTarget.value : null,
            transform: {
                position: this._collectVector('position'),
                rotation: this._collectVector('rotation'),
                scale: this._collectVector('scale'),
            },
        };

        if (this._isEditMode() && this.hasInitialSceneValue && this.initialSceneValue?.id) {
            payload.id = this.initialSceneValue.id;
        }

        const isEdit = this._isEditMode();
        const endpoint = isEdit && this.hasUpdateEndpointValue
            ? this.updateEndpointValue
            : (this.hasScenesEndpointValue ? this.scenesEndpointValue : '/api/ar/scenes');
        const method = isEdit ? 'PUT' : 'POST';

        try {
            const response = await fetch(endpoint, {
                method,
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                const error = await this._extractError(response);
                throw new Error(error || 'Enregistrement impossible.');
            }

            const data = await response.json();
            this._displayShare(data);
            const actionLabel = isEdit ? 'mise à jour' : 'sauvegardée';
            this._setStatus(`Scène ${actionLabel} (id ${data.id}).`, 'success');
            if (isEdit && this.hasInitialSceneValue) {
                this.initialSceneValue = {
                    ...this.initialSceneValue,
                    ...payload,
                    id: data?.id ?? this.initialSceneValue.id,
                };
            }
        } catch (error) {
            console.error(error);
            this._setStatus(error.message || 'Erreur lors de la sauvegarde de la scène.', 'error');
        } finally {
            this._unlockTrigger(trigger, originalLabel);
            this.isSaving = false;
        }
    }

    resetForm(event) {
        event?.preventDefault?.();
        this._resetFormState({ clearShare: true });
    }

    newScene(event) {
        event?.preventDefault?.();
        this._resetFormState({ focusTop: true, clearShare: true });
    }

    cancel(event) {
        event?.preventDefault?.();
        const url = this.hasCancelUrlValue ? this.cancelUrlValue : null;
        if (url) {
            window.location.href = url;
        } else {
            window.history.back();
        }
    }

    assetTypeChanged() {
        const type = this._currentAssetType();
        this._toggleAssetSections(type);
        if (type === 'model') {
            this._initModelSelection();
        } else {
            this.updateAssetFromInput();
        }
        this.renderPlacementPreview();
    }

    updateAssetFromInput() {
        if (!this.hasAssetTarget) {
            return;
        }
        const type = this._currentAssetType();
        let value = '';
        if (type === 'video' && this.hasVideoInputTarget) {
            value = this.videoInputTarget.value.trim();
        } else if (type === 'image' && this.hasImageInputTarget) {
            value = this.imageInputTarget.value.trim();
        }
        this.assetTarget.value = value;
        if (type !== 'model') {
            this.renderPlacementPreview();
        }
    }

    transformChanged() {
        window.requestAnimationFrame(() => this.renderPlacementPreview());
    }

    selectModel(event) {
        event?.preventDefault?.();
        const button = event?.currentTarget instanceof HTMLElement ? event.currentTarget : null;
        if (!button) {
            return;
        }
        this._selectModelButton(button);
        this.renderPlacementPreview();
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

    _parseModels(option) {
        if (!option) {
            return [];
        }

        try {
            const raw = option.getAttribute('data-models') || '';
            if (!raw) {
                return [];
            }

            const decoded = JSON.parse(raw);
            let models = [];
            if (Array.isArray(decoded)) {
                models = decoded;
            } else if (decoded && Array.isArray(decoded.models)) {
                models = decoded.models;
            } else if (decoded && Array.isArray(decoded.items)) {
                models = decoded.items;
            } else {
                return [];
            }

            return models
                .map((item) => this._normalizeModelFromPack(item))
                .filter(Boolean);
        } catch (error) {
            console.warn('Impossible de décoder les modèles du pack MindAR.', error);
            return [];
        }
    }

    _updateModelLibrary(models) {
        if (!this.hasModelChoicesTarget) {
            return;
        }

        const fallback = Array.isArray(this.defaultModelLibrary) ? this.defaultModelLibrary : [];
        const library = Array.isArray(models) && models.length ? models : fallback;

        if (!library.length) {
            this.modelChoicesTarget.innerHTML = '<p class="ar-form__placeholder">Aucun modèle n\'est disponible pour ce pack.</p>';
            if (this.hasAssetTarget) {
                this.assetTarget.value = '';
            }
            if (this.hasVideoInputTarget) {
                this.videoInputTarget.value = '';
            }
            if (this.hasImageInputTarget) {
                this.imageInputTarget.value = '';
            }
            this._highlightModel(null);
            this._updateModelInfo();
            return;
        }

        const hasExplicitDefault = library.some((model) => model && model.default);
        this._renderModelChoices(library, hasExplicitDefault);
        this._highlightModel(null);
        this._updateModelInfo();

        let defaultButton = null;
        if (hasExplicitDefault) {
            defaultButton = this.modelChoiceTargets.find((button) => button.hasAttribute('data-model-default')) ?? null;
        }
        if (!defaultButton) {
            defaultButton = this.modelChoiceTargets[0] ?? null;
        }

        if (defaultButton) {
            this._selectModelButton(defaultButton, { focus: false });
        }
    }

    _renderModelChoices(models, hasExplicitDefault) {
        if (!this.hasModelChoicesTarget) {
            return;
        }

        const html = models
            .map((model, index) => this._buildModelChoice(model, index, hasExplicitDefault))
            .filter(Boolean)
            .join('');

        this.modelChoicesTarget.innerHTML = html;
    }

    _buildModelChoice(model, index, hasExplicitDefault) {
        if (!model || !model.path) {
            return '';
        }

        const name = model.name || this._nameFromPath(model.path);
        const description = model.description || '';
        const emoji = model.emoji || '🧊';
        const type = model.type || 'model';
        const isDefault = Boolean(model.default) || (!hasExplicitDefault && index === 0);

        const attributes = [
            'type="button"',
            'class="model-card"',
            'data-mindar-create-target="modelChoice"',
            'data-action="mindar-create#selectModel"',
            `data-model-path="${this._escapeAttr(model.path)}"`,
            `data-model-name="${this._escapeAttr(name)}"`,
            `data-model-description="${this._escapeAttr(description)}"`,
            `data-model-emoji="${this._escapeAttr(emoji)}"`,
            `data-model-type="${this._escapeAttr(type)}"`,
        ];

        if (model.id) {
            attributes.push(`data-model-id="${this._escapeAttr(model.id)}"`);
        }

        if (model.poster) {
            attributes.push(`data-model-poster="${this._escapeAttr(model.poster)}"`);
        }

        if (isDefault) {
            attributes.push('data-model-default="1"');
        }

        const descriptionHtml = description
            ? `<span class="model-card__description">${this._escapeHtml(description)}</span>`
            : '';

        return `
            <button ${attributes.join(' ')}>
                <span class="model-card__icon" aria-hidden="true">${this._escapeHtml(emoji || '🧊')}</span>
                <span class="model-card__title">${this._escapeHtml(name)}</span>
                ${descriptionHtml}
            </button>
        `;
    }

    _snapshotModelLibrary() {
        if (!this.hasModelChoiceTarget) {
            return [];
        }

        return this.modelChoiceTargets
            .map((button) => ({
                id: button.getAttribute('data-model-id') || '',
                path: button.getAttribute('data-model-path') || '',
                name: button.getAttribute('data-model-name') || '',
                description: button.getAttribute('data-model-description') || '',
                emoji: button.getAttribute('data-model-emoji') || '',
                type: button.getAttribute('data-model-type') || 'model',
                poster: button.getAttribute('data-model-poster') || '',
                default: button.hasAttribute('data-model-default'),
            }))
            .filter((model) => model.path);
    }

    _normalizeModelFromPack(item) {
        if (!item || typeof item !== 'object') {
            return null;
        }

        const path = item.path || item.url || item.source;
        if (!path) {
            return null;
        }

        const mime = item.mime || item.mimetype || '';
        let type = item.type || '';
        if (!type && typeof mime === 'string') {
            if (mime.startsWith('video/')) {
                type = 'video';
            } else if (mime.startsWith('image/')) {
                type = 'image';
            } else if (mime.startsWith('model/')) {
                type = 'model';
            }
        }
        if (!type) {
            type = 'model';
        }

        return {
            id: item.id || item.slug || '',
            path,
            name: item.name || this._nameFromPath(path),
            description: item.description || '',
            emoji: item.emoji || '',
            type,
            poster: item.poster || item.thumbnail || '',
            default: Boolean(item.default || item.isDefault),
        };
    }

    _nameFromPath(path) {
        if (!path) {
            return 'Modèle 3D';
        }
        const segments = path.split(/[\\/]/);
        const last = segments.pop() || path;
        const base = last.split('.')[0] || last;
        const cleaned = base.replace(/[-_]+/g, ' ').trim();
        if (!cleaned) {
            return 'Modèle 3D';
        }
        return cleaned.charAt(0).toUpperCase() + cleaned.slice(1);
    }

    _escapeAttr(value) {
        if (value === null || value === undefined) {
            return '';
        }
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    _escapeHtml(value) {
        if (value === null || value === undefined) {
            return '';
        }
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    _populateThumbs(items) {
        if (!this.hasThumbsTarget) {
            return false;
        }

        this.thumbsTarget.innerHTML = '';
        this.thumbItems = [];

        const normalized = items
            .map((item, index) => this._normalizeThumbItem(item, index))
            .filter(Boolean);

        if (!normalized.length) {
            if (this.hasTargetIndexTarget) {
                this.targetIndexTarget.value = '';
            }
            this.thumbsTarget.insertAdjacentHTML('beforeend', '<p class="ar-form__placeholder">Aucune miniature pour ce pack.</p>');
            this._resetPackPreview();
            this._setTargetInfo('Sélectionnez une image pour confirmer la détection MindAR.');
            return false;
        }

        this.thumbItems = normalized;

        normalized.forEach((item) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'target-thumb';
            btn.dataset.targetIndex = `${item.index ?? 0}`;
            btn.dataset.thumbLabel = item.label ?? '';
            btn.dataset.thumbThumb = item.thumb ?? '';
            btn.dataset.thumbImage = item.image ?? '';
            btn.setAttribute('aria-pressed', 'false');
            const indexValue = Number.isFinite(item.index) ? item.index : Number.parseInt(item.index ?? '0', 10);
            const displayIndex = Number.isFinite(indexValue) ? indexValue + 1 : 1;
            btn.setAttribute('aria-label', `Cible ${displayIndex}${item.label ? ` – ${item.label}` : ''}`);
            btn.innerHTML = `
                 <img src="${item.thumb}" alt="${item.label ?? ''}" class="target-thumb__image" />
                <div class="target-thumb__caption">${item.label ?? 'Cible'}</div>
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

        return true;
    }

    _resetThumbs() {
        if (!this.hasThumbsTarget) {
            return;
        }

        this.thumbsTarget.innerHTML = '<p class="ar-form__placeholder">Aucun pack MindAR détecté pour le moment.</p>';
        if (this.hasTargetIndexTarget) {
            this.targetIndexTarget.value = '';
        }
        this.thumbItems = [];
        this._resetPackPreview();
        this._setTargetInfo('Sélectionnez une image pour confirmer la détection MindAR.');
    }

    _highlight(active) {
        if (!this.hasThumbsTarget) {
            return;
        }

        this.thumbsTarget.querySelectorAll('button').forEach((button) => {
            button.classList.remove('target-thumb--active');
            button.setAttribute('aria-pressed', 'false');
        });
        active.classList.add('target-thumb--active');
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
            image,
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
        this._updatePreviewBackground(source);
    }

    _showPackThumbnail(option) {
        if (!this.hasPackPreviewTarget) {
            return;
        }

        const thumbnail = option?.getAttribute('data-thumbnail');
        if (thumbnail) {
            const caption = this.selectedPackName ? `Pack « ${this.selectedPackName} » sélectionné` : 'Pack MindAR sélectionné';
            this.packPreviewTarget.innerHTML = `
                <figure class="pack-preview">
                    <img src="${thumbnail}" alt="${this.selectedPackName ?? ''}" class="pack-preview__image" />
                        <figcaption class="pack-preview__caption">${caption}<br><span class="pack-preview__hint">Choisissez une cible ci-dessous.</span></figcaption>
                </figure>
            `;
            this._setTargetInfo('Sélectionnez une image pour confirmer la détection MindAR.');
            this._updatePreviewBackground(thumbnail);
        } else {
            this._resetPackPreview();
        }
    }

    _resetPackPreview() {
        if (!this.hasPackPreviewTarget) {
            return;
        }

        this.packPreviewTarget.innerHTML = '<p class="ar-form__hint">Sélectionnez un pack pour afficher un aperçu du motif.</p>';
        this._setTargetInfo('Sélectionnez une image pour confirmer la détection MindAR.');
        this._updatePreviewBackground(null);
    }

    _initModelSelection() {
        if (!this.hasAssetTarget || !this.hasModelChoiceTarget) {
            return;
        }

        const currentValue = this.assetTarget.value?.trim();
        let candidate = null;

        if (currentValue) {
            candidate = this.modelChoiceTargets.find((button) => button.getAttribute('data-model-path') === currentValue) ?? null;
        }

        if (candidate) {
            this._selectModelButton(candidate, { focus: false });
        } else {
            this._highlightModel(null);
            this._updateModelInfo();
        }
    }

    _selectModelButton(button, options = { focus: true }) {
        if (!button || !this.hasAssetTarget) {
            return;
        }

        const path = button.getAttribute('data-model-path');
        const type = button.getAttribute('data-model-type') || 'model';

        this._setAssetType(type);

        if (type === 'video' && this.hasVideoInputTarget) {
            this.videoInputTarget.value = path || '';
        } else if (type === 'image' && this.hasImageInputTarget) {
            this.imageInputTarget.value = path || '';
        } else {
            if (this.hasVideoInputTarget) {
                this.videoInputTarget.value = '';
            }
            if (this.hasImageInputTarget) {
                this.imageInputTarget.value = '';
            }
        }

        this.assetTarget.value = path || '';

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

        if (!active) {
            return;
        }

        active.classList.add('model-card--active');
        active.setAttribute('aria-pressed', 'true');
    }

    _updateModelInfo(button = null) {
        if (!this.hasModelInfoTarget) {
            return;
        }

        if (!button) {
            this.modelInfoTarget.innerHTML = '<p class="ar-form__hint ar-form__hint--small">Sélectionnez un modèle pour afficher ses détails.</p>';
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

    _currentAssetType() {
        if (!this.hasAssetTypeTarget) {
            return 'model';
        }
        const selected = this.assetTypeTargets.find((input) => input.checked);
        return selected ? selected.value : 'model';
    }

    _setAssetType(type) {
        if (!this.hasAssetTypeTarget) {
            return;
        }
        let changed = false;
        this.assetTypeTargets.forEach((input) => {
            if (input.value === type) {
                if (!input.checked) {
                    input.checked = true;
                    changed = true;
                }
            } else {
                input.checked = false;
            }
        });
        if (changed) {
            this._toggleAssetSections(type);
        }
    }

    _currentAssetUrl() {
        if (!this.hasAssetTarget) {
            return '';
        }
        return this.assetTarget.value?.trim() ?? '';
    }

    _toggleAssetSections(type) {
        if (this.hasModelSectionTarget) {
            this._setVisibility(this.modelSectionTarget, type === 'model');
        }
        if (this.hasVideoSectionTarget) {
            this._setVisibility(this.videoSectionTarget, type === 'video');
        }
        if (this.hasImageSectionTarget) {
            this._setVisibility(this.imageSectionTarget, type === 'image');
        }
    }

    _setVisibility(element, visible) {
        if (!element) {
            return;
        }
        element.classList.toggle('hidden', !visible);
    }

    _resetFormState(options = {}) {
        const { focusTop = false, clearShare = false } = {
            focusTop: false,
            clearShare: false,
            ...options,
        };

        if (this.hasFormTarget) {
            this.formTarget.reset();
        }

        this.selectedPackName = null;

        if (this.hasTitleTarget) {
            this.titleTarget.value = '';
            this.titleTarget.placeholder = this._defaultTitle();
        }
        if (this.hasPackTarget) {
            if (this.packTarget.options.length > 0) {
                this.packTarget.selectedIndex = 0;
            } else {
                this.packTarget.value = '';
            }
        }
        if (this.hasAssetTarget) {
            this.assetTarget.value = '';
        }
        if (this.hasVideoInputTarget) {
            this.videoInputTarget.value = '';
        }
        if (this.hasImageInputTarget) {
            this.imageInputTarget.value = '';
        }
        if (this.hasMindfileTarget) {
            this.mindfileTarget.value = '';
        }
        if (this.hasSoundTarget) {
            this.soundTarget.value = '';
        }
        if (this.hasTargetIndexTarget) {
            this.targetIndexTarget.value = '';
        }

        this._resetThumbs();
        this._highlightModel(null);
        this._updateModelInfo();
        this._toggleAssetSections(this._currentAssetType());
        this._renderPreviewPlaceholder();

        if (clearShare) {
            this._hideSharePanel();
        }

        this._clearStatus();

        if (focusTop) {
            window.requestAnimationFrame(() => {
                this.element?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        }
    }

    _collectVector(kind) {
        const vector = { x: 0, y: 0, z: 0 };
        if (kind === 'position') {
            if (this.hasPositionXTarget) vector.x = this._readFloat(this.positionXTarget.value, 0);
            if (this.hasPositionYTarget) vector.y = this._readFloat(this.positionYTarget.value, 0);
            if (this.hasPositionZTarget) vector.z = this._readFloat(this.positionZTarget.value, 0);
        } else if (kind === 'rotation') {
            if (this.hasRotationXTarget) vector.x = this._readFloat(this.rotationXTarget.value, 0);
            if (this.hasRotationYTarget) vector.y = this._readFloat(this.rotationYTarget.value, 0);
            if (this.hasRotationZTarget) vector.z = this._readFloat(this.rotationZTarget.value, 0);
        } else if (kind === 'scale') {
            if (this.hasScaleXTarget) vector.x = this._readFloat(this.scaleXTarget.value, 1);
            if (this.hasScaleYTarget) vector.y = this._readFloat(this.scaleYTarget.value, 1);
            if (this.hasScaleZTarget) vector.z = this._readFloat(this.scaleZTarget.value, 1);
        }
        return vector;
    }

    _readFloat(value, fallback) {
        const parsed = Number.parseFloat(value);
        return Number.isFinite(parsed) ? parsed : fallback;
    }

    _formatVector(vector) {
        return `${vector.x} ${vector.y} ${vector.z}`;
    }

    _buildPlacementPreview(assetType, assetUrl, position, rotation, scale) {
        const assetDeclaration = this._buildAssetDeclaration(assetType, assetUrl);
        const assetEntity = this._buildAssetEntity(assetType);

        return `
            <div class="preview-stage">
                <a-scene embedded renderer="colorManagement:true" background="color: #f0f0f0" vr-mode-ui="enabled:false" style="width:100%;height:100%;">
                    <a-assets>
                        ${assetDeclaration}
                    </a-assets>
                     <a-entity light="type: ambient; color: #ffffff; intensity: 0.6"></a-entity>
                    <a-entity light="type: directional; color: #ffffff; intensity: 0.7" position="0 1 1"></a-entity>
                    <a-entity position="0 1.2 2.5">
                        <a-camera wasd-controls-enabled="false" look-controls="enabled:false" rotation="-18 0 0"></a-camera>
                    </a-entity>
                    <a-entity rotation="-90 0 0">
                       <a-plane color="#ffffff" height="2" width="2" material="opacity:0.85; transparent:true"></a-plane>
                        <a-ring color="#a0aec0" radius-inner="0.02" radius-outer="0.04" position="0 0.01 0"></a-ring>
                    </a-entity>
                    <a-entity position="${position}" rotation="${rotation}" scale="${scale}">
                        ${assetEntity}
                    </a-entity>
                </a-scene>
            </div>
        `;
    }

    _buildAssetDeclaration(assetType, assetUrl) {
        const safeUrl = assetUrl.replace(/"/g, '&quot;');
        switch (assetType) {
            case 'video':
                return `<video id="previewAsset" src="${safeUrl}" preload="auto" loop muted playsinline crossorigin="anonymous"></video>`;
            case 'image':
                return `<img id="previewAsset" src="${safeUrl}" crossorigin="anonymous" alt="Aperçu 2D" />`;
            case 'model':
            default:
                return `<a-asset-item id="previewAsset" src="${safeUrl}" crossorigin="anonymous"></a-asset-item>`;
        }
    }

    _buildAssetEntity(assetType) {
        switch (assetType) {
            case 'video':
                return '<a-video src="#previewAsset" width="1" height="0.56" autoplay="true"></a-video>';
            case 'image':
                return '<a-image src="#previewAsset" width="1" height="1"></a-image>';
            case 'model':
            default:
                return '<a-gltf-model src="#previewAsset"></a-gltf-model>';
        }
    }

    _displayShare(data) {
        if (!data) {
            return;
        }

        if (this.hasShareLinkTarget && data.shareUrl) {
            this.shareLinkTarget.href = data.shareUrl;
        }
        if (this.hasExperienceLinkTarget && data.experienceUrl) {
            this.experienceLinkTarget.href = data.experienceUrl;
        }
        if (this.hasShareQrTarget) {
            if (data.qr) {
                this.shareQrTarget.innerHTML = `<img src="${data.qr}" alt="QR code de l'expérience" class="share-panel__qr-image"/>`;
            } else {
                this.shareQrTarget.innerHTML = '';
            }
        }
        if (this.hasSharePanelTarget) {
            this.sharePanelTarget.classList.remove('hidden');
        }
    }

    _unlockTrigger(trigger, originalLabel) {
        if (!trigger) {
            return;
        }
        trigger.disabled = false;
        trigger.classList.remove('is-busy');
        if (originalLabel !== undefined && originalLabel !== null) {
            trigger.textContent = originalLabel;
        }
    }

    _setStatus(message, tone = 'info') {
        if (!this.hasStatusTarget) {
            return;
        }

        const element = this.statusTarget;
        element.textContent = message;
        element.classList.remove('hidden', 'ar-form__status--success', 'ar-form__status--error');

        if (tone === 'error') {
            element.classList.add('ar-form__status--error');
        } else {
            element.classList.add('ar-form__status--success');
        }
    }

    _clearStatus() {
        if (!this.hasStatusTarget) {
            return;
        }

        const element = this.statusTarget;
        element.textContent = '';
        element.classList.add('hidden');
        element.classList.remove('ar-form__status--success', 'ar-form__status--error');
    }


    _hideSharePanel() {
        if (this.hasSharePanelTarget) {
            this.sharePanelTarget.classList.add('hidden');
        }
        if (this.hasShareLinkTarget) {
            this.shareLinkTarget.removeAttribute('href');
        }
        if (this.hasExperienceLinkTarget) {
            this.experienceLinkTarget.removeAttribute('href');
        }
        if (this.hasShareQrTarget) {
            this.shareQrTarget.innerHTML = '';
        }
    }

    _ensureAssetSelected() {
        const assetUrl = this._currentAssetUrl();
        if (assetUrl) {
            return true;
        }
        const type = this._currentAssetType();
        if (type === 'model') {
            alert('Sélectionne un modèle 3D dans la bibliothèque.');
        } else if (type === 'video') {
            alert('Renseigne une URL de vidéo (mp4/webm) pour la scène.');
        } else {
            alert('Renseigne une URL d\'image (png/jpg) pour la scène.');
        }
        return false;
    }

    _renderPreviewPlaceholder() {
        if (!this.hasPreviewTarget) {
            return;
        }
        this.previewTarget.innerHTML = `
            <div class="preview-placeholder">
                <p><em>Sélectionnez un média (modèle 3D, vidéo ou image) pour visualiser son placement.</em></p>
            </div>
        `;
        this._applyPreviewBackground();
    }

    _updatePreviewBackground(source) {
        this.previewBackground = source || null;
        this._applyPreviewBackground();
    }

    _applyPreviewBackground() {
        if (!this.hasPreviewTarget) {
            return;
        }
        const container = this.previewTarget;
        if (this.previewBackground) {
            container.style.backgroundImage = `url('${this.previewBackground}')`;
            container.style.backgroundSize = 'cover';
            container.style.backgroundPosition = 'center';
            container.style.backgroundRepeat = 'no-repeat';
        } else {
            container.style.backgroundImage = '';
            container.style.backgroundSize = '';
            container.style.backgroundPosition = '';
            container.style.backgroundRepeat = '';
        }
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

        if (this.persistedMindTargetPath) {
            return { type: 'persisted', path: this.persistedMindTargetPath, packName: this.selectedPackName };
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
                let normalizedPath = url.pathname.replace(/\\\\/g, '/');
                const idx = normalizedPath.toLowerCase().indexOf('/public/');
                if (idx !== -1) {
                    const relative = normalizedPath.substring(idx + '/public'.length);
                    return relative.startsWith('/') ? relative : `/${relative}`;
                }
                return normalizedPath.startsWith('/') ? normalizedPath : `/${normalizedPath}`;
            } catch (error) {
                console.warn('Impossible de normaliser le chemin MindAR', error);
                const fallback = path.replace(/^file:\/\//, '').replace(/\\\\/g, '/');
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
            throw new Error(error || "Upload du fichier .mind impossible.");
        }

        const data = await response.json();
        if (!data?.path) {
            throw new Error("Réponse inattendue de l'upload MindAR.");
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
