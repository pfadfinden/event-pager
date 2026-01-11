import { Controller } from '@hotwired/stimulus';


export default class extends Controller {
    static targets = ["collectionContainer", "template", "optionsList"]

    static values = {
        index    : Number,
        prototype: String,
        preselected: String,
    }

    addSelectedRecipient(event) {
        if (event.target.disabled) {
            return;
        }
        // TODO bug double click outside option, adds all
        const selectedId = event.target.value;
        const label = event.target.textContent;
        this._addSelectedRecipientToTable(selectedId, label);
        this._markSelected(event.target);
    }

    addSelectedRecipients(event) {
        const options = event.target.options;
        for (let i= 0, iLen= options.length; i < iLen; i++) {
            const option = options[i];

            if (option.selected && !option.disabled) {
                this._addSelectedRecipientToTable(option.value, option.text);
                this._markSelected(option);
                option.selected = false;
            }
        }
    }

    _addSelectedRecipientToTable(id, label)
    {
        const item = this.templateTarget.content.cloneNode(true);
        let td = item.querySelectorAll("td");
        td[0].querySelector("input[data-property='id']").value = id;
        td[0].querySelector("input[data-property='label']").value = label;
        td[0].innerHTML = td[0].innerHTML.replace(/__name__/g, this.indexValue);
        td[2].textContent = label;

        this.collectionContainerTarget.appendChild(item);
        this.indexValue++;
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
        console.log(this.preselectedValue);
        JSON.parse(this.preselectedValue).forEach(recipientId => {
            target.querySelectorAll('option[value=\'' +recipientId + '\']')
                    .forEach(o => this._markSelected(o));
        })
    }
}