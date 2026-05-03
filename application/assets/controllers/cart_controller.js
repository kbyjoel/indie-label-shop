import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { count: Number, addUrl: String };
    static targets = ['countBadge', 'form', 'submitButton', 'feedback'];

    connect() {
        this._syncBadge();
    }

    countValueChanged() {
        this._syncBadge();
    }

    async add(event) {
        event.preventDefault();

        const form = event.target.closest('form') ?? this.formTarget;
        const variantIdInput = form.querySelector('[name="variantId"]');
        const qtyInput = form.querySelector('[name="qty"]');

        const variantId = variantIdInput ? parseInt(variantIdInput.value, 10) : 0;
        const qty = qtyInput ? parseInt(qtyInput.value, 10) : 1;

        if (!variantId) return;

        if (this.hasSubmitButtonTarget) this.submitButtonTarget.disabled = true;

        try {
            const res = await fetch(this._addUrl(), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ variantId, qty }),
            });
            const data = await res.json();
            if (data.success) {
                this.countValue = data.cartCount;
                document.dispatchEvent(new CustomEvent('cart:item-added', {
                    bubbles: false,
                    detail: { cartCount: data.cartCount },
                }));
                this._showFeedback('✓', false);
            }
        } catch (_) {
            // silently ignore network errors
        } finally {
            if (this.hasSubmitButtonTarget) this.submitButtonTarget.disabled = false;
        }
    }

    async remove(event) {
        const url = event.currentTarget.dataset.removeUrl;
        const itemId = event.currentTarget.dataset.itemId;
        if (!url || !itemId) return;

        const res = await fetch(url, { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            this.countValue = data.cartCount;
            document.getElementById('cart-item-' + itemId)?.remove();
            this._updateCartTotal(data.cartTotal);
        }
    }

    async update(event) {
        const input = event.currentTarget;
        const url = input.dataset.updateUrl;
        const itemId = input.dataset.itemId;
        const qty = parseInt(input.value, 10);
        if (!url || !itemId) return;

        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ qty }),
        });
        const data = await res.json();
        if (data.success) {
            this.countValue = data.cartCount;
            if (qty <= 0) {
                document.getElementById('cart-item-' + itemId)?.remove();
            } else {
                const subtotal = document.getElementById('subtotal-' + itemId);
                if (subtotal) subtotal.textContent = this._formatPrice(data.itemTotal);
            }
            this._updateCartTotal(data.cartTotal);
        }
    }

    _syncBadge() {
        for (const badge of document.querySelectorAll('#cart-count, [data-cart-target="countBadge"]')) {
            badge.textContent = this.countValue > 0 ? this.countValue : '0';
        }
    }

    _updateCartTotal(totalCents) {
        const el = document.getElementById('cart-total');
        if (el && totalCents != null) el.textContent = this._formatPrice(totalCents);
    }

    _showFeedback(message, isError) {
        if (!this.hasFeedbackTarget) return;
        this.feedbackTarget.textContent = message;
        this.feedbackTarget.className = 'mt-3 text-sm ' + (isError ? 'text-red-500' : 'text-accent');
        this.feedbackTarget.classList.remove('hidden');
        setTimeout(() => this.feedbackTarget.classList.add('hidden'), 2000);
    }

    _addUrl() {
        return this.addUrlValue || '/panier/ajouter';
    }

    _formatPrice(cents) {
        return (cents / 100).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
    }
}
