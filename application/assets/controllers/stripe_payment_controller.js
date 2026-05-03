import { Controller } from '@hotwired/stimulus';
import { loadStripe } from '@stripe/stripe-js';

export default class extends Controller {
    static values = {
        publishableKey: String,
        clientSecret: String,
        paymentConfirmUrl: String,
    };

    static targets = ['cardElement', 'submitButton', 'errorMessage'];

    _stripe = null;
    _cardElement = null;

    async clientSecretValueChanged() {
        if (!this.clientSecretValue || !this.publishableKeyValue) return;
        await this._mountCard();
    }

    disconnect() {
        if (this._cardElement) {
            this._cardElement.destroy();
            this._cardElement = null;
        }
    }

    async _mountCard() {
        if (this._cardElement) {
            this._cardElement.destroy();
            this._cardElement = null;
        }

        this._stripe = await loadStripe(this.publishableKeyValue);
        const elements = this._stripe.elements();

        this._cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#1f2937',
                    fontFamily: 'ui-sans-serif, system-ui, sans-serif',
                    '::placeholder': { color: '#9ca3af' },
                },
                invalid: { color: '#ef4444' },
            },
        });

        this._cardElement.mount(this.cardElementTarget);

        this._cardElement.on('change', (event) => {
            if (this.hasErrorMessageTarget) {
                if (event.error) {
                    this.errorMessageTarget.textContent = event.error.message;
                    this.errorMessageTarget.classList.remove('hidden');
                } else {
                    this.errorMessageTarget.classList.add('hidden');
                }
            }
        });

        if (this.hasSubmitButtonTarget) {
            this.submitButtonTarget.disabled = false;
        }
    }

    async pay(event) {
        event.preventDefault();

        if (!this._stripe || !this._cardElement) return;

        this._setLoading(true);

        const { paymentIntent, error } = await this._stripe.confirmCardPayment(
            this.clientSecretValue,
            { payment_method: { card: this._cardElement } },
        );

        if (error) {
            if (this.hasErrorMessageTarget) {
                this.errorMessageTarget.textContent = error.message;
                this.errorMessageTarget.classList.remove('hidden');
            }
            this._setLoading(false);
            return;
        }

        const res = await fetch(this.paymentConfirmUrlValue, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ paymentIntentId: paymentIntent.id }),
        });

        const data = await res.json();
        if (data.success && data.redirectUrl) {
            window.location.href = data.redirectUrl;
        } else {
            if (this.hasErrorMessageTarget) {
                this.errorMessageTarget.textContent = data.error ?? 'Payment failed.';
                this.errorMessageTarget.classList.remove('hidden');
            }
            this._setLoading(false);
        }
    }

    _setLoading(loading) {
        if (!this.hasSubmitButtonTarget) return;
        this.submitButtonTarget.disabled = loading;
        if (loading) {
            this.submitButtonTarget.dataset.originalLabel = this.submitButtonTarget.textContent;
            this.submitButtonTarget.textContent = this.submitButtonTarget.dataset.loadingLabel ?? '...';
        } else {
            this.submitButtonTarget.textContent = this.submitButtonTarget.dataset.originalLabel ?? this.submitButtonTarget.dataset.label ?? 'Pay';
        }
    }
}
