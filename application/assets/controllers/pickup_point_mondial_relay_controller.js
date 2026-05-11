import { Controller } from '@hotwired/stimulus';

// Phase 2: implement Mondial Relay PickupWidget v4
// https://widget.mondialrelay.com/parcelshop-picker/v4_B2C/script/widget-min.js
export default class extends Controller {
    static targets = ['externalId', 'name', 'address', 'summary', 'submitBtn'];
    static values = { config: Object };

    connect() {
        const container = this.element.querySelector('#relay-widget-container');
        if (container) {
            container.innerHTML = `
                <div class="rounded border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">
                    <p class="font-semibold text-gray-700">Widget Mondial Relay</p>
                    <p class="mt-1">Intégration à implémenter en Phase 2.</p>
                </div>
            `;
        }
    }
}
