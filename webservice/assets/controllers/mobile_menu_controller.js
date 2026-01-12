import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['overlay', 'iconMenu', 'iconClose'];

    connect() {
        this.isOpen = false;
        this.boundHandleKeydown = this.handleKeydown.bind(this);
    }

    toggle(event) {
        this.isOpen = !this.isOpen;
        this.updateState(event.currentTarget);
    }

    close() {
        if (this.isOpen) {
            this.isOpen = false;
            this.updateState(this.element.querySelector('[data-action*="toggle"]'));
        }
    }

    updateState(button) {
        this.overlayTarget.classList.toggle('is-open', this.isOpen);
        this.overlayTarget.setAttribute('aria-hidden', !this.isOpen);

        if (button) {
            button.setAttribute('aria-expanded', this.isOpen);
        }

        this.iconMenuTarget.classList.toggle('d-none', this.isOpen);
        this.iconCloseTarget.classList.toggle('d-none', !this.isOpen);

        if (this.isOpen) {
            document.body.style.overflow = 'hidden';
            document.addEventListener('keydown', this.boundHandleKeydown);
        } else {
            document.body.style.overflow = '';
            document.removeEventListener('keydown', this.boundHandleKeydown);
        }
    }

    handleKeydown(event) {
        if (event.key === 'Escape') {
            this.close();
        }
    }

    disconnect() {
        document.removeEventListener('keydown', this.boundHandleKeydown);
        document.body.style.overflow = '';
    }
}
