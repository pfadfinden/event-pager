import {Controller} from '@hotwired/stimulus';
import {useWindowResize, useDebounce} from 'stimulus-use';

/**
 * Stimulus controller for keyboard shortcuts to select predefined messages.
 *
 * This controller listens for number based keyboard shortcuts and navigates
 * to the corresponding predefined message link found on the page.
 *
 * Modifier keys shift & ctrl may be necessary when in a typing context.
 *
 * Usage:
 *   Add the controller to a parent element:
 *   <div data-controller="predefined-message-shortcuts">
 *
 *   Mark links with the data attribute:
 *   <a data-predefined-shortcut="1" href="/send?predefined=...">1. Fire Alert</a>
 *   <a data-predefined-shortcut="2" href="/send?predefined=...">2. Weather Report</a>
 */
export default class extends Controller  {
    static debounces = ['windowResize']

    connect() {
        useDebounce(this)
        useWindowResize(this)
        this.handleKeydown = this.handleKeydown.bind(this);
    }

    disconnect() {
        document.removeEventListener('keydown', this.handleKeydown);
    }

    windowResize() {
        if (this.element.checkVisibility()) {
            document.addEventListener('keydown', this.handleKeydown);
        } else {
            document.removeEventListener('keydown', this.handleKeydown);
        }
    }

    handleKeydown(event) {
        // Get the number pressed (1-9)
        const keyNumber = parseInt(event.key, 10);
        if (isNaN(keyNumber) || keyNumber < 1 || keyNumber > 9) {
            return;
        }

        if (
            (
                event.target instanceof HTMLInputElement
                || event.target instanceof HTMLTextAreaElement
                || (event.target instanceof HTMLElement && event.target.isContentEditable)
            )
        ) {
            // We are typing, ignore
            return;
        }

        // Find the link with the matching shortcut number anywhere in the document
        const item = document.querySelector(`[data-predefined-shortcut="${keyNumber}"]`);
        if (item && item.href) {
            event.preventDefault();
            window.location.href = item.href;
        }
    }
}
