import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['stripeFields', 'paypalFields', 'emptyMessage'];

    connect() {
        this.toggleFields();

        const select = document.querySelector('[data-payment-gateway-select]');
        if (!select) return;

        if (window.jQuery) {
            this._handler = () => this.toggleFields();
            window.jQuery(select).on('change', this._handler);
        } else {
            this._handler = () => this.toggleFields();
            select.addEventListener('change', this._handler);
        }
    }

    disconnect() {
        const select = document.querySelector('[data-payment-gateway-select]');
        if (!select || !this._handler) return;

        if (window.jQuery) {
            window.jQuery(select).off('change', this._handler);
        } else {
            select.removeEventListener('change', this._handler);
        }
    }

    toggleFields() {
        const select = document.querySelector('[data-payment-gateway-select]');
        const type = select ? select.value : null;

        this.stripeFieldsTarget.style.display = type === 'stripe' ? '' : 'none';
        this.paypalFieldsTarget.style.display = type === 'paypal' ? '' : 'none';

        if (this.hasEmptyMessageTarget) {
            this.emptyMessageTarget.style.display = (type === 'stripe' || type === 'paypal') ? 'none' : '';
        }
    }
}
