import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["search", "optionsList"]

    static values = {
        endpoint: String,
    }

    fetchOptions() {
        const query = this.searchTarget.value

        const url = new URLSearchParams();
        url.append('search', query);


        fetch(this.endpointValue + '?' + url.toString())
            .then(response => response.text()
                .then(jsonList => JSON.parse(jsonList))
                .then(jsonList => this._addSearchResultValues(jsonList))
            );
    }

    _addSearchResultValues(searchResults)
    {
        const items = searchResults.map(value => {
            const item = document.createElement('option')
            item.setAttribute('value', value.id);
            item.setAttribute('data-enabled-transports', JSON.stringify(value.enabledTransports || []));
            item.innerHTML = value.name;
            return item;
        })
        this.optionsListTarget.replaceChildren(...items);
    }
}