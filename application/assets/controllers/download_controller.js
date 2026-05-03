import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        prepareUrl: String,
        orderItemId: Number,
    };
    static targets = ['formatInput', 'button', 'status', 'spinner', 'statusText'];

    #pollInterval = null;

    connect() {
        this._resetStatus();
    }

    disconnect() {
        this._stopPolling();
    }

    async submit(event) {
        event.preventDefault();

        const formatInput = this.formatInputTargets.find(i => i.checked);
        if (!formatInput) return;

        const format = formatInput.value;
        this._setLoading(true);
        this._setStatus('pending', '');

        try {
            const res = await fetch(this.prepareUrlValue, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ orderItemId: this.orderItemIdValue, format }),
            });

            const data = await res.json();

            if (!res.ok) {
                this._setStatus('failed', data.error ?? 'Une erreur est survenue.');
                this._setLoading(false);
                return;
            }

            if (data.status === 'ready' && data.url) {
                this._triggerDownload(data.url);
                this._setStatus('ready', '');
                this._setLoading(false);
                return;
            }

            if (data.token) {
                this._startPolling(data.token);
            }
        } catch (_) {
            this._setStatus('failed', 'Erreur réseau.');
            this._setLoading(false);
        }
    }

    _startPolling(token) {
        const statusUrl = this.prepareUrlValue.replace('/prepare', '/status/') + token;
        this._pollInterval = setInterval(async () => {
            try {
                const res = await fetch(statusUrl);
                if (!res.ok) { this._stopPolling(); return; }

                const data = await res.json();

                if (data.status === 'ready' && data.url) {
                    this._stopPolling();
                    this._triggerDownload(data.url);
                    this._setStatus('ready', '');
                    this._setLoading(false);
                } else if (data.status === 'failed') {
                    this._stopPolling();
                    this._setStatus('failed', data.message ?? 'La génération a échoué.');
                    this._setLoading(false);
                } else {
                    this._setStatus(data.status, '');
                }
            } catch (_) {
                // keep polling on transient network error
            }
        }, 2000);
    }

    _stopPolling() {
        if (this._pollInterval) {
            clearInterval(this._pollInterval);
            this._pollInterval = null;
        }
    }

    _triggerDownload(url) {
        const a = document.createElement('a');
        a.href = url;
        a.download = '';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    _setLoading(loading) {
        if (this.hasButtonTarget) this.buttonTarget.disabled = loading;
        if (this.hasSpinnerTarget) this.spinnerTarget.classList.toggle('hidden', !loading);
    }

    _setStatus(status, message) {
        if (!this.hasStatusTarget) return;
        this.statusTarget.className = 'mt-2 text-sm';
        this.statusTarget.classList.remove('hidden');

        const labels = {
            pending: 'En attente…',
            processing: 'Encodage en cours…',
            ready: 'Prêt — téléchargement démarré.',
            failed: message || 'Échec de la génération.',
        };

        if (this.hasStatusTextTarget) {
            this.statusTextTarget.textContent = labels[status] ?? status;
        }

        if (status === 'failed') {
            this.statusTarget.classList.add('text-red-500');
        } else if (status === 'ready') {
            this.statusTarget.classList.add('text-accent');
        } else {
            this.statusTarget.classList.add('text-muted');
        }
    }

    _resetStatus() {
        if (this.hasStatusTarget) this.statusTarget.classList.add('hidden');
        if (this.hasSpinnerTarget) this.spinnerTarget.classList.add('hidden');
    }
}
