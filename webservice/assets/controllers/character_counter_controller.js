import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'help'];
    static values = {
        thresholds: { type: Array, default: [] }
    };

    connect() {
        this.update();
    }

    update() {
        const length = this.inputTarget.value.length;
        const thresholds = this.thresholdsValue;

        // Find the highest threshold that is met
        let activeMessage = null;
        for (const threshold of thresholds) {
            if (length >= threshold.min) {
                activeMessage = threshold;
            }
        }

        if (activeMessage) {
            this.helpTarget.textContent = activeMessage.message;
            this.helpTarget.className = 'form-text ' + (activeMessage.class || 'text-muted');
        }
    }
}
