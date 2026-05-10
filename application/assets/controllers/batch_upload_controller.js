import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { tracks: Array };
    static targets = ['input', 'tbody', 'submit', 'preview'];

    fileInputChange(event) {
        const files = Array.from(event.target.files);
        if (files.length === 0) {
            this._reset();
            return;
        }

        files.sort((a, b) => a.name.localeCompare(b.name, undefined, { numeric: true, sensitivity: 'base' }));

        const maxPosition = this.tracksValue.length > 0
            ? Math.max(...this.tracksValue.map(t => t.position))
            : 0;

        this.tbodyTarget.innerHTML = '';

        files.forEach((file, i) => {
            const track = this.tracksValue[i] ?? null;
            const position = track ? track.position : maxPosition + (i - this.tracksValue.length + 1);
            const title = track ? track.title : this._parseTitleFromFilename(file.name);
            const isNew = !track;
            const hasExistingMaster = track && track.hasMaster;

            const row = document.createElement('tr');
            row.innerHTML =
                `<td>${position}</td>` +
                `<td>${this._escape(title)}${isNew ? ' <span class="badge bg-primary">Nouvelle track</span>' : ''}</td>` +
                `<td>${this._escape(file.name)}</td>` +
                `<td>${hasExistingMaster ? '<span class="badge bg-warning text-dark">Remplace le master existant</span>' : ''}</td>`;
            this.tbodyTarget.appendChild(row);
        });

        this.previewTarget.classList.remove('d-none');
        this.submitTarget.disabled = false;
    }

    _reset() {
        this.tbodyTarget.innerHTML = '';
        this.previewTarget.classList.add('d-none');
        this.submitTarget.disabled = true;
    }

    _parseTitleFromFilename(name) {
        const withoutExt = name.replace(/\.[^.]+$/, '');
        return withoutExt.replace(/^\d+[\s\-_.]+/, '').trim();
    }

    _escape(str) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
}
