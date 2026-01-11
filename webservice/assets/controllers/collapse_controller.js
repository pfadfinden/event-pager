import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['content'];

    toggle(event) {
        const row = event.currentTarget;
        const isExpanded = row.getAttribute('aria-expanded') === 'true';

        row.setAttribute('aria-expanded', !isExpanded);
        this.contentTarget.classList.toggle('d-none', isExpanded);
    }
}
