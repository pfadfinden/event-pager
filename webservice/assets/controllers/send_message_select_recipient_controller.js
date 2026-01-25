import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["collectionContainer", "template", "optionsList"]

    static values = {
        index    : Number,
        prototype: String,
        preselected: String,
    }

    addSelectedRecipient(event) {
        // Only handle double-clicks on actual option elements
        if (event.target.tagName !== 'OPTION') {
            return;
        }
        if (event.target.disabled) {
            return;
        }
        const selectedId = event.target.value;
        const label = event.target.textContent;
        const enabledTransports = this._getEnabledTransports(event.target);
        this._addSelectedRecipientToTable(selectedId, label, enabledTransports);
        this._markSelected(event.target);
    }

    addSelectedRecipients(event) {
        const options = event.target.options;
        for (let i= 0, iLen= options.length; i < iLen; i++) {
            const option = options[i];

            if (option.selected && !option.disabled) {
                const enabledTransports = this._getEnabledTransports(option);
                this._addSelectedRecipientToTable(option.value, option.text, enabledTransports);
                this._markSelected(option);
                option.selected = false;
            }
        }
    }

    _getEnabledTransports(optionElement) {
        const transportsData = optionElement.getAttribute('data-enabled-transports');
        if (transportsData) {
            try {
                return JSON.parse(transportsData);
            } catch (e) {
                return [];
            }
        }
        return [];
    }

    _addSelectedRecipientToTable(id, label, enabledTransports = [])
    {
        const item = this.templateTarget.content.cloneNode(true);
        let td = item.querySelectorAll("td");
        td[0].querySelector("input[data-property='id']").value = id;
        td[0].querySelector("input[data-property='label']").value = label;
        td[0].querySelector("input[data-property='enabledTransports']").value = JSON.stringify(enabledTransports);
        td[0].innerHTML = td[0].innerHTML.replace(/__name__/g, this.indexValue);
        td[2].textContent = label;

        // Show only enabled transport icons, remove others
        this._filterTransportIcons(td[3], enabledTransports);

        this.collectionContainerTarget.appendChild(item);
        this.indexValue++;
    }

    _filterTransportIcons(container, enabledTransports) {
        const hasTransports = enabledTransports && enabledTransports.length > 0;

        // Show/hide the "none" placeholder
        const nonePlaceholder = container.querySelector('[data-transport-none]');
        if (nonePlaceholder) {
            if (hasTransports) {
                nonePlaceholder.remove();
            }
        }

        // Remove icons for transports that are not enabled
        container.querySelectorAll('[data-transport]').forEach(icon => {
            const transport = icon.getAttribute('data-transport');
            if (enabledTransports.includes(transport)) {
                icon.classList.remove('d-none');
            } else {
                icon.remove();
            }
        });
    }

    /**
     * Event handler for remove button in table
     *
     * @param event
     */
    removeSelected(event) {
        this.collectionContainerTarget.querySelectorAll('tr').forEach(element => {
            if (element.contains(event.target)) {
                const recipientId = element.querySelector("input[data-property='id']").value;
                this._deselectRecipient(recipientId);
                element.remove()
                this.indexValue--
            }
        })
    }

    /**
     * When removing a recipient, de-highlight it in the combobox
     *
     * @param recipientId
     * @private
     */
    _deselectRecipient(recipientId) {
        this.optionsListTarget
            .querySelectorAll('option[value=\'' +recipientId + '\']')
            .forEach(o => this._markUnselected(o))
    }

    _markSelected(element) {
        element.classList.add('text-success');
        element.disabled = true;
    }

    _markUnselected(element) {
        element.classList.remove('fw-semibold','text-success');
        element.disabled = false;
    }

    /**
     * When initialising the form, mark all options in the Combobox that were preselected in the table
     *
     * @param target HTMLSelectElement
     */
    optionsListTargetConnected(target) {
        JSON.parse(this.preselectedValue).forEach(recipientId => {
            target.querySelectorAll('option[value=\'' +recipientId + '\']')
                    .forEach(o => this._markSelected(o));
        })
    }
}
