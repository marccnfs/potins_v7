import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['audio', 'anchor', 'status', 'toggle'];

    connect() {
        this.audioEnabled = false;
        this.anchorVisible = false;

        this.onTargetFound = () => {
            this.anchorVisible = true;
            this.playAudio();
        };
        this.onTargetLost = () => {
            this.anchorVisible = false;
            this.stopAudio();
        };
        this.onSoundReady = () => {
            if (this.audioEnabled && this.anchorVisible) {
                this.playAudio();
            }
        };

        if (this.hasAnchorTarget) {
            this.anchorTarget.addEventListener('targetFound', this.onTargetFound);
            this.anchorTarget.addEventListener('targetLost', this.onTargetLost);
        }
        if (this.hasAudioTarget) {
            this.audioTarget.addEventListener('loaded', this.onSoundReady);
            this.audioTarget.addEventListener('sound-loaded', this.onSoundReady);
        }

        this.updateUi();
    }

    disconnect() {
        if (this.hasAnchorTarget) {
            this.anchorTarget.removeEventListener('targetFound', this.onTargetFound);
            this.anchorTarget.removeEventListener('targetLost', this.onTargetLost);
        }
        if (this.hasAudioTarget) {
            this.audioTarget.removeEventListener('loaded', this.onSoundReady);
            this.audioTarget.removeEventListener('sound-loaded', this.onSoundReady);
        }
    }

    toggleAudio(event) {
        event.preventDefault();
        this.audioEnabled = !this.audioEnabled;
        this.updateUi();
        if (this.audioEnabled) {
            this.playAudio();
        } else {
            this.stopAudio();
        }
    }

    playAudio() {
        const sound = this.soundComponent;
        if (!sound || !this.audioEnabled) {
            return;
        }
        if (!this.anchorVisible) {
            return;
        }
        if (!sound.isPlaying) {
            sound.playSound();
        }
    }

    stopAudio() {
        const sound = this.soundComponent;
        if (!sound) {
            return;
        }
        if (sound.isPlaying) {
            sound.stopSound();
        }
    }

    updateUi() {
        if (this.hasToggleTarget) {
            this.toggleTarget.textContent = this.audioEnabled ? 'Couper le son' : 'Activer le son';
            this.toggleTarget.setAttribute('aria-pressed', String(this.audioEnabled));
        }
        if (this.hasStatusTarget) {
            this.statusTarget.textContent = this.audioEnabled
                ? 'Son activé : pointe la caméra vers la cible pour lancer l’ambiance.'
                : 'Son désactivé. Active-le puis scanne la cible pour écouter l’ambiance.';
        }
    }

    get soundComponent() {
        return this.audioTarget?.components?.sound ?? null;
    }
}
