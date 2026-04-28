import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['typeSelect', 'configBlock'];

    connect() {
        this.toggleConfig();
        if (window.jQuery) {
            this._handler = () => this.toggleConfig();
            window.jQuery(this.typeSelectTarget).on('change', this._handler);
        }
    }

    disconnect() {
        if (window.jQuery && this._handler) {
            window.jQuery(this.typeSelectTarget).off('change', this._handler);
        }
    }

    toggleConfig() {
        const selected = this.typeSelectTarget.value;
        this.configBlockTargets.forEach(block => {
            block.style.display = block.dataset.configType === selected ? '' : 'none';
        });
    }
}
