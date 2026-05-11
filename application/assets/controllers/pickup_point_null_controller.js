import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['externalId', 'name', 'address', 'summary', 'submitBtn'];
    static values = { config: Object };

    connect() {
        // Dev/test gateway: auto-fill with the first fake pickup point
        this.externalIdTarget.value = 'null-001';
        this.nameTarget.value = 'Le Relais du Centre';
        this.addressTarget.value = '12 rue de la Paix';

        const container = this.element.querySelector('#relay-widget-container');
        if (container) {
            container.innerHTML = `
                <div class="rounded border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">
                    <p class="font-medium text-gray-700">NullShippingGateway — mode test</p>
                    <p class="mt-1">Point pré-sélectionné : <strong>Le Relais du Centre</strong></p>
                    <p class="text-xs mt-1">Config : ${JSON.stringify(this.configValue)}</p>
                </div>
            `;
        }

        this.summaryTarget.hidden = false;
        this.submitBtnTarget.disabled = false;
    }
}
