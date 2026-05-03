import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        clientId: String,
        currency: { type: String, default: 'EUR' },
        createOrderUrl: String,
        captureOrderUrl: String,
    };

    static targets = ['buttonsContainer', 'errorMessage'];

    _sdkLoaded = false;

    clientIdValueChanged() {
        if (!this.clientIdValue) return;
        this._loadSdk();
    }

    _loadSdk() {
        const src = `https://www.paypal.com/sdk/js?client-id=${this.clientIdValue}&currency=${this.currencyValue}`;

        if (document.querySelector(`script[src="${src}"]`)) {
            // SDK already present — render buttons immediately
            this._renderButtons();
            return;
        }

        const script = document.createElement('script');
        script.src = src;
        script.onload = () => this._renderButtons();
        script.onerror = () => this._showError('Failed to load PayPal SDK.');
        document.head.appendChild(script);
    }

    _renderButtons() {
        if (!window.paypal || !this.hasButtonsContainerTarget) return;

        // Clear any previously rendered buttons
        this.buttonsContainerTarget.innerHTML = '';

        window.paypal.Buttons({
            createOrder: async () => {
                const res = await fetch(this.createOrderUrlValue, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                });
                if (!res.ok) {
                    this._showError('Could not create PayPal order.');
                    throw new Error('create_order_failed');
                }
                const data = await res.json();
                return data.id;
            },

            onApprove: async (data) => {
                const res = await fetch(this.captureOrderUrlValue, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ orderId: data.orderID }),
                });
                const result = await res.json();
                if (result.success && result.redirectUrl) {
                    window.location.href = result.redirectUrl;
                } else {
                    this._showError(result.error ?? 'PayPal payment failed.');
                }
            },

            onError: (err) => {
                console.error('PayPal error:', err);
                this._showError('An error occurred with PayPal. Please try again.');
            },

            onCancel: () => {
                // User cancelled — no action needed
            },
        }).render(this.buttonsContainerTarget);
    }

    _showError(message) {
        if (this.hasErrorMessageTarget) {
            this.errorMessageTarget.textContent = message;
            this.errorMessageTarget.classList.remove('hidden');
        }
    }
}
