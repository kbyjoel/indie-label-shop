import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { miniUrl: String };
    static targets = ['panel', 'items', 'total'];

    connect() {
        this._boundOnItemAdded = this._onItemAdded.bind(this);
        this._boundOnOutsideClick = this._onOutsideClick.bind(this);
        this._boundOnKeydown = this._onKeydown.bind(this);

        document.addEventListener('cart:item-added', this._boundOnItemAdded);
    }

    disconnect() {
        document.removeEventListener('cart:item-added', this._boundOnItemAdded);
        document.removeEventListener('mousedown', this._boundOnOutsideClick);
        document.removeEventListener('keydown', this._boundOnKeydown);
    }

    async open(event) {
        event.preventDefault();

        if (this.hasPanelTarget && !this.panelTarget.classList.contains('hidden')) {
            this._hide();
            return;
        }

        await this._fetchAndShow();
    }

    async _onItemAdded() {
        if (window.location.pathname.includes('/panier')) return;
        await this._fetchAndShow();
    }

    async _fetchAndShow() {
        try {
            const res = await fetch(this.miniUrlValue);
            if (!res.ok) return;
            const data = await res.json();
            this._render(data);
            this._show();
        } catch (_) {
            // popover is enhancement-only — fail silently
        }
    }

    _render(data) {
        if (this.hasTotalTarget) {
            this.totalTarget.textContent = this._formatPrice(data.total);
        }

        if (!this.hasItemsTarget) return;

        this.itemsTarget.innerHTML = '';

        for (const item of data.items) {
            const li = document.createElement('li');
            li.className = 'flex items-center gap-3 py-2';

            const imgHtml = item.image
                ? `<img src="${item.image}" alt="${this._escape(item.name)}" class="w-10 h-10 rounded object-cover shrink-0">`
                : `<div class="w-10 h-10 rounded bg-border shrink-0"></div>`;

            const variantHtml = item.variantLabel
                ? `<span class="text-xs text-muted block">${this._escape(item.variantLabel)}</span>`
                : '';

            li.innerHTML = `
                ${imgHtml}
                <div class="flex-1 min-w-0">
                    <span class="text-sm font-medium truncate block">${this._escape(item.name)}</span>
                    ${variantHtml}
                </div>
                <span class="text-sm shrink-0">${this._formatPrice(item.lineTotal)}</span>
            `;
            this.itemsTarget.appendChild(li);
        }
    }

    _show() {
        if (!this.hasPanelTarget) return;
        this.panelTarget.classList.remove('hidden');
        document.addEventListener('mousedown', this._boundOnOutsideClick);
        document.addEventListener('keydown', this._boundOnKeydown);
    }

    _hide() {
        if (!this.hasPanelTarget) return;
        this.panelTarget.classList.add('hidden');
        document.removeEventListener('mousedown', this._boundOnOutsideClick);
        document.removeEventListener('keydown', this._boundOnKeydown);
    }

    _onOutsideClick(event) {
        if (!this.element.contains(event.target)) this._hide();
    }

    _onKeydown(event) {
        if (event.key === 'Escape') this._hide();
    }

    _formatPrice(cents) {
        return (cents / 100).toLocaleString('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }) + ' €';
    }

    _escape(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
}
