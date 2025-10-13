import { Controller } from '@hotwired/stimulus';
export default class extends Controller {
    static values = { selector: String };
    toggle(){
        const el = document.querySelector(this.selectorValue || '[sound]');
        const sound = el?.components?.sound;
        if (sound) (sound.isPlaying ? sound.stopSound() : sound.playSound());
    }
}
