import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { variants: Array };
    static targets = ['optionSelect', 'priceDisplay', 'addButton', 'variantIdInput'];

    connect() {
        this._update();
    }

    optionSelectTargetConnected() {
        this._update();
    }

    change() {
        this._update();
    }

    _update() {
        const variant = this._findMatchingVariant();

        if (this.hasPriceDisplayTarget) {
            this.priceDisplayTarget.textContent = variant?.price != null
                ? this._formatPrice(variant.price)
                : '—';
        }

        if (this.hasAddButtonTarget) {
            this.addButtonTarget.disabled = variant === null || variant.stock === 0;
        }

        if (this.hasVariantIdInputTarget) {
            this.variantIdInputTarget.value = variant?.id ?? '';
        }
    }

    _findMatchingVariant() {
        const selectedIds = this.optionSelectTargets.map(sel => parseInt(sel.value, 10));

        return this.variantsValue.find(v => {
            if (v.optionValueIds.length !== selectedIds.length) return false;
            return selectedIds.every(id => v.optionValueIds.includes(id));
        }) ?? null;
    }

    _formatPrice(cents) {
        return (cents / 100).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
    }
}
