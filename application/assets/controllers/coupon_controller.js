import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'codeInput',
        'applyButton',
        'activeZone',
        'activeCode',
        'activeAmount',
        'errorZone',
        'adjustmentsList',
        'promotionsTotal',
        'cartTotal',
    ];

    static values = {
        applyUrl:  String,
        removeUrl: String,
    };

    async applyCoupon(event) {
        event.preventDefault();
        const code = this.codeInputTarget.value.trim();
        if (!code) return;

        this.clearError();
        this.applyButtonTarget.disabled = true;

        try {
            const res = await fetch(this.applyUrlValue, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ coupon_code: code }),
            });
            const data = await res.json();

            if (data.success) {
                this.updateTotals(data);
                this.codeInputTarget.value = '';
            } else {
                this.showError(data.message);
            }
        } catch {
            this.showError('Une erreur est survenue. Veuillez réessayer.');
        } finally {
            this.applyButtonTarget.disabled = false;
        }
    }

    async removeCoupon(event) {
        event.preventDefault();

        try {
            const res = await fetch(this.removeUrlValue, {
                method: 'DELETE',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json();
            if (data.success) {
                this.updateTotals(data);
            }
        } catch {
            // silent fail — page reload will restore correct state
        }
    }

    showError(message) {
        if (this.hasErrorZoneTarget) {
            this.errorZoneTarget.textContent = message;
            this.errorZoneTarget.classList.remove('hidden');
        }
    }

    clearError() {
        if (this.hasErrorZoneTarget) {
            this.errorZoneTarget.textContent = '';
            this.errorZoneTarget.classList.add('hidden');
        }
    }

    updateTotals(data) {
        // Update adjustments list
        if (this.hasAdjustmentsListTarget) {
            this.adjustmentsListTarget.innerHTML = data.adjustments.map(adj => this.renderAdjustment(adj)).join('');
        }

        // Show/hide active coupon zone
        if (this.hasActiveZoneTarget) {
            if (data.coupon_code) {
                const removable = data.adjustments.find(a => a.removable);
                this.activeZoneTarget.classList.remove('hidden');
                if (this.hasActiveCodeTarget) this.activeCodeTarget.textContent = data.coupon_code;
                if (this.hasActiveAmountTarget && removable) {
                    this.activeAmountTarget.textContent = this.formatPrice(removable.amount);
                }
            } else {
                this.activeZoneTarget.classList.add('hidden');
            }
        }

        // Update totals
        if (this.hasPromotionsTotalTarget) {
            this.promotionsTotalTarget.closest('[data-promotions-row]')?.classList.toggle('hidden', data.promotions_total === 0);
            this.promotionsTotalTarget.textContent = this.formatPrice(data.promotions_total);
        }
        if (this.hasCartTotalTarget) {
            this.cartTotalTarget.textContent = this.formatPrice(data.total);
        }
    }

    renderAdjustment(adj) {
        const amount = this.formatPrice(adj.amount);
        const removeBtn = adj.removable
            ? `<button type="button" class="ml-2 text-gray-400 hover:text-red-500" data-action="coupon#removeCoupon">×</button>`
            : '';
        return `<tr data-promotions-row>
            <td class="py-1 text-sm text-gray-600">${adj.label}${removeBtn}</td>
            <td class="py-1 text-sm text-right text-green-700">${amount}</td>
        </tr>`;
    }

    formatPrice(cents) {
        return (cents / 100).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
    }
}
