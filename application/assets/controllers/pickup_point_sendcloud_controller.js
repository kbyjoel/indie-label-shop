import { Controller } from '@hotwired/stimulus';

// Phase 3: implement Sendcloud Service Point Picker
// https://embed.sendcloud.sc/spp/1.0.0/api.min.js
export default class extends Controller {
    static targets = ['externalId', 'name', 'address', 'summary', 'submitBtn'];
    static values = { config: Object };

    connect() {
        const container = this.element.querySelector('#relay-widget-container');
        if (container) {
            container.innerHTML = `
                <div class="rounded border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">
                    <p class="font-semibold text-gray-700">Widget Sendcloud</p>
                    <p class="mt-1">Intégration à implémenter en Phase 3.</p>
                </div>
            `;
        }
    }
}
