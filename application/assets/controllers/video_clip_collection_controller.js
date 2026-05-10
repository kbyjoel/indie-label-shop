import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['preview'];

    connect() {
        this._lastCode = null;
        this.renderPreview();
        this._handler = () => setTimeout(() => this.renderPreviewIfChanged(), 0);
        document.addEventListener('hidden.bs.offcanvas', this._handler);
    }

    disconnect() {
        document.removeEventListener('hidden.bs.offcanvas', this._handler);
    }

    renderPreviewIfChanged() {
        const textarea = this.element.querySelector('.collection-form-container textarea');
        const code = textarea?.value?.trim() ?? '';
        if (code !== this._lastCode) {
            this.renderPreview();
        }
    }

    renderPreview() {
        const textarea = this.element.querySelector('.collection-form-container textarea');
        const code = textarea?.value?.trim() ?? '';
        this._lastCode = code;

        if (code) {
            this.previewTarget.innerHTML = code;
            const iframe = this.previewTarget.querySelector('iframe');
            if (iframe) {
                iframe.removeAttribute('width');
                iframe.removeAttribute('height');
            }
        } else {
            this.previewTarget.innerHTML = `
                <div class="d-flex flex-column align-items-center justify-content-center text-white-50 w-100 h-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="52" height="52" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM6.79 5.093A.5.5 0 0 0 6 5.5v5a.5.5 0 0 0 .79.407l3.5-2.5a.5.5 0 0 0 0-.814l-3.5-2.5z"/>
                    </svg>
                    <small class="mt-2">Cliquer pour ajouter un clip</small>
                </div>`;
        }
    }
}
