/**
 * @module @drupal/autocomplete
 */

import Combobox from './combobox.js';

/**
 * A standalone autocomplete, optimized for accessibility and querying remote
 * sources.
 *
 * @example <!-- Ensure the assets are included in the page. -->
 * <link rel="stylesheet" href="[path]/a11y.autocomplete.css" />
 * <script src="[path]/a11y.autocomplete.js"></script>
 *
 * @example <!-- Initialization -->
 * <!-- Element to receive autocomplete functionality. -->
 * <input id="an-input" />
 *
 * <script>
 *   const input = document.querySelector('#an-input');
 *
 *   // Initialize the autocomplete with a fixed list of items.
 *   const autocompleteInstanceFixedList = A11yAutocomplete(input, {
 *     source: ['first item', 'second item', 'third item'],
 *   });
 *
 *   // Or initialize the autocomplete with dynamic results.
 *   const autocompleteInstanceFixedList = A11yAutocomplete(input, {
 *     source: (query, result) => {
 *       result(['first item', 'second item', 'third item']);
 *     },
 *   });
 *
 *   // When the autocomplete is initialized, markup is added.
 * </script>
 *
 * @example
 * <!-- The input is wrapped in a div, making is possible to position the results list with CSS. -->
 * <div data-drupal-autocomplete-wrapper>
 *   <!-- Several attributes are added to the input, used for accessibility and being identifiable by JavaScript. -->
 *   <input id="an-input" aria-autocomplete="list" autocomplete="off" data-drupal-autocomplete-input aria-owns="autocomplete-listbox-0" role="combobox" aria-expanded="false" aria-describedby="assistive-hint-0">
 *     <!-- This span provides assitive technology such as screenreaders with additional information when the input is focused. -->
 *     <span class="visually-hidden" id="assistive-hint-0">Type 2 or more characters for results. When autocomplete results are available use up and down arrows to review and enter to select. Touch device users, explore by touch or with swipe gestures.</span>
 *     <!-- This is the <ul> that will list the query results when characters are typed in the input. -->
 *     <ul role="listbox" data-drupal-autocomplete-list="" id="autocomplete-listbox-0" hidden=""></ul>
 *     <!-- This is a live region, used for conveying the results of interactions to assistive technology, such as the number of results available after typing. -->
 *     <span data-drupal-autocomplete-live-region="" aria-live="assertive"></span>
 * </div>
 *
 * @example <!-- Setting Options -->
 * <!-- Options can be set in three ways, listed from highest precedence to lowest: -->
 *
 * <!-- 1. An object literal in the input's `data-autocomplete` attribute with the format {camelCaseOptionName: value}. -->
 * <input data-autocomplete='{"minChars": "3", "source":["first item", "second item", "third item"]}' />
 *
 * <!-- 2. Via the data-autocomplete-(hyphen delimited option name) attribute. -->
 * <input data-autocomplete-min-chars="3" data-autocomplete-source="['first item', 'second item', 'third item']" />
 *
 * <script>
 *   // 3. Via the options argument when initializing a new instance
 *   A11yAutocomplete(input, {maxItems: 10, , source: ['first item', 'second item', 'third item']})
 * </script>
 */
/**
 * Options sent to the Autocomplete constructor that will override the default
 * options.
 * @typedef {Object} A11yAutocomplete~Options
 * @property {Array|string|A11yAutocomplete~sourceCallback} [source] - An array or a string containing
 *  JSON that parses into an array of values to search when the user types in
 *  the input field, or a function to take what the user types and call a
 *  callback function with the results to be displayed.
 * @property {string|null} [allowRepeatValues=null] - If `true`,
 *  autocomplete results can include items already included in the field. A null
 *  value functions the same as false, but a null value can be used to determine
 *  if this value was explicitly set or using defaults.
 * @property {Boolean} [autoFocus=false] - When `true`, the first result is
 *  focused as soon as a list of results becomes visible.
 * @property {string} [separatorChar=','] - The character used to separate
 *  multiple values in the same form.
 * @property {string} [firstCharacterIgnoreList=','] - Any characters in this
 *  string will not be incorporated in a search as the first character of a
 *  query. Typically, this string should at least include the value of
 *  `separatorChar`.
 * @property {Number} [minChars=1] - Minimum number of characters that must
 *  be typed before displaying autocomplete results.
 * @property {Number} [cardinality=1] - The number of values the input can
 *  reference, where multiple values are separated by the character specified in
 *  the `separatorChar` option. Set to `-1` for unlimited cardinality.
 * @property {Boolean} [createLiveRegion=true] - When `true`, initialization
 *  includes adding a live region specifically that will convey autocomplete activity
 *  to assistive technology. This is typically only set to `false` if a site has
 *  a centralized live region for assistive technology announcements.
 * @property {string} [inputAssistiveHint='When autocomplete results areavailable use up and down arrows to review and enter to select. Touch device users, explore by touch or with swipe gestures.'] -
 *  Message conveyed to assistive technology when the input is focused.
 * @property {string} [minCharAssistiveHint='Type @count or more characters for results'] -
 *  When `minChars` is greater than one, this is appended to
 *  `inputAssistiveHint` so users are aware how many characters are
 *  needed to trigger a search.  `@count` is replaced with the value of`minChars`.
 * @property {string} [noResultsAssistiveHint='No results found'] - Message
 *  conveyed to assistive technology when the query returns no results.
 * @property {string} [someResultsAssistiveHint='There are @count results available.'] -
 *  Message conveyed to assistive technology when the number of results exceeds
 *  one. `@count` is replaced with the number of results returned.
 * @property {string} [oneResultAssistiveHint='There is one result available.'] -
 *  Message conveyed to assistive technology when there is one result.
 *
 */
class _A11yAutocomplete {
  /**
   * Construct a new A11yAutocomplete class.
   *
   * @param {HTMLElement} input
   *   The element to be used as an autocomplete.
   * @param {A11yAutocomplete~Options} options
   *  Autocomplete options.
   */
  constructor(input, options = {}) {
    this.input = input;

    this.supportedOptions = [
      'source',
      'cardinality',
      'minChars',
      'separatorChar',
      'firstCharacterIgnoreList',
      'createLiveRegion',
      'autoFocus',
      'allowRepeatValues',
      // messages.
      'minCharAssistiveHint',
      'inputAssistiveHint',
      'noResultsAssistiveHint',
      'someResultsAssistiveHint',
      'oneResultAssistiveHint',
      'highlightedAssistiveHint',
    ];

    const defaultOptions = {
      // from jquery ui
      autoFocus: false,
      firstCharacterIgnoreList: ',',
      minChars: 1,
      // API already sort results
      sort: false,
      // shim
      displayLabels: true,
      // shim
      disabled: false,
      source: [],
      cardinality: 1,
      // shim
      inputClass: '',
      // shim
      ulClass: '',
      // shim
      itemClass: '',
      // shim
      loadingClass: '',
      separatorChar: ',',
      // for drupal
      createLiveRegion: true,
      // shim
      listZindex: 100,
      // to pass jquery ui tests
      allowRepeatValues: null,
      searchDelay: 300,
      // to nest later.
      minCharAssistiveHint: 'Type @count or more characters for results',
      inputAssistiveHint:
        'When autocomplete results are available use up and down arrows to review and enter to select. Touch device users, explore by touch or with swipe gestures.',
      noResultsAssistiveHint: 'No results found',
      someResultsAssistiveHint: 'There are @count results available.',
      oneResultAssistiveHint: 'There is one result available.',
      highlightedAssistiveHint:
        '@selectedItem @position of @count is highlighted',
    };

    this.options = {
      ...defaultOptions,
      ...this.filterOptions(options),
      ...this.filterOptions(this.attributesToOptions()),
    };

    // Preset lists provided as strings should be converted to options.
    if (typeof this.options.source === 'string') {
      this.options.source = JSON.parse(this.options.source);
    }

    if (
      typeof this.options.source !== 'function' &&
      !Array.isArray(this.options.source)
    ) {
      throw new TypeError('options.source is not an array or a function.');
    }

    this.orignialValue = this.input.value;
    this.selected = null;
    this.suggestionItems = [];
    this.searchTimeOutId = null;

    this.combobox = new Combobox(this.input, this.options);

    this.initEventsHandlers();

    /**
     * The API to manage an input's autocomplete functionality.
     *
     * @typedef {Object} A11yAutocomplete~Api
     * @property {A11yAutocomplete~destroy} destroy - Function to destroy the autocomplete
     *  instance.
     * @property {string} id - The id of the instance.
     */
    this.api = {
      destroy: this.destroy.bind(this),
      id: this.input.id,
      /**
       * Do not use! only for jquery shim.
       *
       * @deprecated
       */
      _internal_object: this,
    };

    /**
     * Fires after initialization and markup additions.
     *
     * @event A11yAutocomplete#autocomplete-created
     * @property {Class} autocomplete - The autocomplete instance.
     */
    this.triggerEvent('autocomplete-created');
  }

  initEventsHandlers() {
    // Events to add.
    this.events = {
      input: {
        input: (e) => this.inputListener(e),
        blur: (e) => this.blurHandler(e),
        'option-selected': (e) => this.optionSelectedListener(e),
      },
    };

    Object.keys(this.events).forEach((elementName) => {
      Object.keys(this.events[elementName]).forEach((eventName) => {
        this[elementName].addEventListener(
          eventName,
          this.events[elementName][eventName],
        );
      });
    });

    // This is necessary to be able to remove the event listener.
    this.boundFocusinHandler = this.focusinHandler.bind(this);

    // Monitor listbox opening to trigger related events.
    this.observer = new MutationObserver((mutationsList) => {
      for (const mutation of mutationsList) {
        if (mutation.attributeName === 'aria-expanded') {
          const listbox = document.querySelector(
            `#${this.input.getAttribute('aria-owns')}`,
          );
          if (
            mutation.target.getAttribute(mutation.attributeName) === 'false'
          ) {
            if (listbox) {
              listbox.removeEventListener('focusin', this.boundFocusinHandler);
            }
            /**
             * Fires after the suggestion list closes.
             *
             * @event A11yAutocomplete#autocomplete-close
             * @property {Class} autocomplete - The autocomplete instance.
             */
            this.triggerEvent('autocomplete-close');
          } else {
            listbox.addEventListener('focusin', this.boundFocusinHandler);
            /**
             * Fires after the suggestion list opens.
             *
             * @event A11yAutocomplete#autocomplete-open
             * @property {Class} autocomplete - The autocomplete instance.
             * // should add the result list in the event maybe?
             */
            this.triggerEvent('autocomplete-open');
          }
        }
      }
    });
    // Start observing the target node for configured mutations
    this.observer.observe(this.input, { attributes: true });
  }

  /**
   * Only accept a subset of supported options.
   *
   * @param options
   */
  filterOptions(options) {
    const filteredOptions = {};
    const rejectedOptions = {};
    // Later loop on the supported options array instead,
    // need to see rejected options to help debug jquery shim for now.
    Object.keys(options).forEach((key) => {
      if (this.supportedOptions.includes(key)) {
        filteredOptions[key] = options[key];
      } else {
        rejectedOptions[key] = options[key];
      }
    });
    // Temporary to help debug the shim.
    if (Object.keys(rejectedOptions).length) {
      console.warn('Rejected autocomplete options: ', rejectedOptions);
    }
    return filteredOptions;
  }

  /**
   * Converts data-autocomplete* attributes into options.
   *
   * @return {object} an autocomplete options object.
   * @private
   */
  attributesToOptions() {
    const options = {};
    // Any options provided in the `data-autocomplete` attribute will take
    // precedence over those specified in `data-autocomplete-(x)`.
    const dataAutocompleteAttributeOptions = this.input.dataset.autocomplete
      ? JSON.parse(this.input.dataset.autocomplete)
      : {};
    const dataset = this.input.dataset;
    // Loop through all of the input's attributes. Any attributes beginning with
    // `data-autocomplete` will be added to an options object.
    for (const key in dataset) {
      if (
        key.includes('autocomplete') &&
        key !== 'autocomplete' &&
        key !== 'autocompleteInput'
      ) {
        let optionName = key.replace('autocomplete', '');
        optionName = optionName.charAt(0).toLowerCase() + optionName.slice(1);
        const value = dataset[key];
        if (['true', 'false'].includes(value)) {
          options[optionName] = value === 'true';
        } else if (['cardinality', 'minChars'].includes(optionName)) {
          // Transform strings to number
          options[optionName] = +value;
        } else {
          options[optionName] = value;
        }
      }
    }
    return { ...options, ...dataAutocompleteAttributeOptions };
  }

  /**
   * Handles blur events.
   *
   * @fires A11yAutocomplete#autocomplete-change
   */
  blurHandler() {
    // Only trigger the event when the value is changed.
    if (this.input.value === this.orignialValue) {
      return;
    }
    // Only trigger the event if the listbox is closed.
    if (this.input.getAttribute('aria-expanded') === 'false') {
      this.orignialValue = this.input.value;
      /**
       * Fires after an item is blurred and another item hasn't been highlighted.
       *
       * @event A11yAutocomplete#autocomplete-change
       * @property {Class} autocomplete - The autocomplete instance.
       */
      this.triggerEvent('autocomplete-change');
    }
  }

  focusinHandler(e) {
    const option = e.target.closest('[role="option"]');
    if (option) {
      this.triggerEvent('autocomplete-highlight', {
        selected: this.suggestions[option.dataset.itemIndex],
      });
    }
  }

  optionSelectedListener(event) {
    const { detail } = event;
    this.selected = detail.value;
    /**
     * Fires when an item is selected for addition. This event can be canceled
     * and prevent the addition of the item.
     *
     * @event A11yAutocomplete#autocomplete-select
     * @property {Class} autocomplete - The autocomplete instance.
     * @property {Object} toSelect - The item selected, as an object with
     *   'label' and 'value' properties.
     */
    let selected = this.triggerEvent(
      'autocomplete-select',
      {
        selected: this.selected,
      },
      true,
      event,
    );
    // If the input can have multiple values, let this class handle setting
    // the input value. If the cardinality is one, let the combobox replace
    // the input value.
    if (this.options.cardinality !== 1) {
      event.preventDefault();
    }
    if (selected) {
      this.replaceInputValue(this.selected);
    }
  }
  /**
   * Replaces the value of an input field when a new value is chosen.
   *
   * @param {object} selected
   *   The element with the item to be added.
   */
  replaceInputValue(selected) {
    const value = selected.value;
    const separator = this.separator();
    if (separator.length > 0) {
      const before = this.previousItems(separator);
      this.input.value = `${before}${value}`;
    } else {
      this.input.value = value;
    }
  }

  /**
   * Returns the separator character.
   *
   * @return {string}
   *   The separator character or a zero-length string.
   *   If the autocomplete input does not support multiple items or has reached
   *   The maximum number of items that can be added, a zero-length string is
   *   returned as no separator is needed.
   */
  separator() {
    const { cardinality } = this.options;
    const numItems = this.splitValues().length - 1;
    return numItems < cardinality || cardinality <= 0
      ? this.options.separatorChar
      : '';
  }

  /**
   * Gets all existing items in the autocomplete input.
   *
   * @param {string} separator
   *   The character separating the items.
   *
   * @return {string|string}
   *   The string of existing values in the input.
   */
  previousItems(separator) {
    // Needs some explanations for the regex here.
    const escapedSeparator = separator.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(`^.+${escapedSeparator}\\s*|`);
    const match = this.inputValue().match(regex)[0];
    return match && match.length > 0 ? `${match.trim()} ` : '';
  }

  /**
   * Takes input events and has them trigger searches when appropriate.
   *
   * @param {Event} e
   *   The input event.
   */
  inputListener(e) {
    if (!this.searchTimeOutId || this.options.searchDelay === 0) {
      this.searchTimeOutId = setTimeout(() => {
        this.doSearch(e);
        this.searchTimeOutId = null;
      }, this.options.searchDelay);
    }
  }

  /**
   * Helper method.
   *
   * @param element
   * @param classes
   */
  addClasses(element, classes) {
    classes &&
      classes
        .split(' ')
        .forEach((className) => element.classList.add(className));
  }

  /**
   * Helper method.
   *
   * @param element
   * @param classes
   */
  removeClasses(element, classes) {
    classes &&
      classes
        .split(' ')
        .forEach((className) => element.classList.remove(className));
  }

  /**
   * Triggers an autocomplete search.
   *
   * @param {Event} e
   *   The event triggering the search.
   */
  doSearch(e) {
    if (this.options.disabled) {
      return;
    }
    const searchTerm = this.extractLastInputValue();
    if (searchTerm && searchTerm.length < this.options.minChars) {
      return;
    }

    /**
     * Fires just before a search. Can be used to cancel the search.
     *
     * @event A11yAutocomplete#autocomplete-pre-search
     * @property {Class} autocomplete - The autocomplete instance.
     */
    if (!this.triggerEvent('autocomplete-pre-search', {}, true, e)) {
      return;
    }

    if (searchTerm && searchTerm.length > 0) {
      if (typeof this.options.source === 'function') {
        this.addClasses(this.input, this.options.loadingClass);
        /**
         * A callback to return autocomplete results dynamically.
         *
         * @callback A11yAutocomplete~sourceCallback
         * @param {string} searchTerm - The search term being searched for.
         * @param {A11yAutocomplete~results} results - A callback to populate
         *  the results list.
         */
        /**
         * @callback A11yAutocomplete~results
         * @param {array} results - An array of results.
         */
        this.options.source(searchTerm, (results) => {
          this.removeClasses(this.input, this.options.loadingClass);
          this.suggestionItems = results;
          this.displayResults();
        });
      } else if (Array.isArray(this.options.source)) {
        // If a predefined list was provided as an option, make this the
        // suggestion items.
        this.suggestionItems = this.options.source;
        this.displayResults();
      } else {
        throw new TypeError('options.source is not an array or a function.');
      }
    } else {
      // If the search query is empty, provide an empty list of suggestions.
      this.suggestionItems = [];
      this.displayResults();
    }
  }

  displayResults() {
    const typed = this.extractLastInputValue();
    this.suggestions = this.suggestionItems;
    let results = this.suggestions;
    if (typed && this.suggestionItems.length > 0) {
      results = this.prepareSuggestionList(typed);
    }
    if (results.length) {
      /**
       * Fires after suggestion items are retrieved, but before they are added to the DOM.
       *
       * @event A11yAutocomplete#autocomplete-response
       * @property {Class} autocomplete - The autocomplete instance.
       * @property {Object[]} list - an array of suggestions as objects with 'label'
       *  and 'value' properties.
       */
      this.triggerEvent('autocomplete-response', {
        list: results,
      });
      this.combobox.displayResults(results);
    } else {
      this.combobox.close();
    }
  }

  /**
   * Converts all suggestions into an object with value and label properties.
   */
  normalizeSuggestionItems() {
    this.suggestionItems = this.suggestionItems.map((item) => {
      if (typeof item === 'string') {
        item = { value: item, label: item };
      } else if (item.value && !item.label) {
        item = { value: item.value, label: item.value };
      } else if (item.label && !item.value) {
        item = { value: item.label, label: item.label };
      }

      return item;
    });
  }

  /**
   * Creates a suggestion list based on a typed value.
   *
   * @param {string} typed
   *   The typed value querying autocomplete.
   */
  prepareSuggestionList(typed) {
    this.normalizeSuggestionItems();
    if (typed) {
      this.suggestions = this.suggestionItems.filter((item) =>
        this.filterResults(item, typed),
      );
    } else {
      this.suggestions = this.suggestionItems;
    }
    if (this.options.sort !== false) {
      this.sortSuggestions();
    }

    return this.suggestions;
  }

  /**
   * Sorts the array of suggestions.
   */
  sortSuggestions() {
    this.suggestions.sort((prior, current) =>
      prior.label.toUpperCase() > current.label.toUpperCase() ? 1 : -1,
    );
  }

  /**
   * Returns the last value of an multi-value textfield.
   *
   * @return {string}
   *   The last value of the input field.
   */
  extractLastInputValue() {
    return this.splitValues().pop();
  }

  /**
   * Gets the input value.
   *
   * @return {String}
   *   The input value.
   */
  inputValue() {
    return this.input.value;
  }

  /**
   * Helper splitting selections from the autocomplete value.
   *
   * @return {Array}
   *   Array of values, split by comma.
   */
  splitValues() {
    const value = this.inputValue();
    const result = [];
    let quote = false;
    let current = '';
    const valueLength = value.length;
    for (let i = 0; i < valueLength; i++) {
      const character = value.charAt(i);
      if (character === '"') {
        current += character;
        quote = !quote;
      } else if (character === this.options.separatorChar && !quote) {
        result.push(current.trim());
        current = '';
      } else {
        current += character;
      }
    }
    if (value.length > 0) {
      result.push(current.trim());
    }
    return result;
  }

  /**
   * Determines if a suggestion should be an available option.
   *
   * @param {object} suggestion
   *   A suggestion based on user input. It is an object with label and value
   *   properties.
   * @param {string} typed
   *   The text entered in the input field.
   *
   * @return {boolean}
   *   If the suggestion should be displayed in the results.
   */
  filterResults(suggestion, typed) {
    const { firstCharacterIgnoreList, cardinality } = this.options;
    const suggestionValue = suggestion.value;
    const currentValues = this.splitValues();

    // Prevent suggestions if the first input character is in the ignore list,
    // if the suggestion has already been added to the field, or if the maximum
    // number of items have been reached.
    if (
      firstCharacterIgnoreList.indexOf(typed[0]) !== -1 ||
      (cardinality > 0 && currentValues.length > cardinality) ||
      (currentValues.indexOf(suggestionValue) !== -1 &&
        !this.options.allowRepeatValues)
    ) {
      return false;
    }

    return RegExp(
      this.extractLastInputValue()
        .trim()
        .replace(/[-\\^$*+?.()|[\]{}]/g, '\\$&'),
      'i',
    ).test(suggestionValue);
  }

  /**
   * Remove all event listeners added by this class.
   * @callback A11yAutocomplete~destroy
   */
  destroy() {
    this.observer.disconnect();
    Object.keys(this.events).forEach((elementName) => {
      Object.keys(this.events[elementName]).forEach((eventName) => {
        this[elementName].removeEventListener(
          eventName,
          this.events[elementName][eventName],
        );
      });
    });
    this.combobox.destroy();

    /**
     * Fires after the instance is destroyed.
     *
     * @event A11yAutocomplete#autocomplete-destroy
     * @property {Class} autocomplete - What remains of the autocomplete instance.
     */
    this.triggerEvent('autocomplete-destroy');
  }

  /**
   * Dispatches an autocomplete event
   *
   * @param {string} type
   *   The event type.
   * @param {object} additionalData
   *   Additional data attached to the event's `details` property.
   * @param {boolean} cancelable
   *   If the dispatched event should be cancelable.
   * @param {Event} originalEvent
   *   A native event that called the function that triggers a custom event.
   *
   * @return {boolean}
   *   If the event triggered successfully.
   */
  triggerEvent(type, additionalData = {}, cancelable = false, originalEvent) {
    const event = new CustomEvent(type, {
      // we should probably set that to allow use of delegated events.
      bubbles: true,
      detail: {
        // Only exposed the "supported" set of methods/options.
        autocomplete: this.api,
        ...additionalData,
      },
      cancelable,
      originalEvent,
    });
    if (originalEvent) {
      event.originalEvent = originalEvent;
    }

    return this.input.dispatchEvent(event);
  }
}

/**
 * Main entrypoint to the library.
 *
 * @global
 *
 * @param {HTMLElement} input
 *   The element to be used as an autocomplete.
 * @param {A11yAutocomplete~Options} options
 *  Autocomplete options.
 * @return {A11yAutocomplete~Api}
 *  API to manage an input's autocomplete functionality.
 *
 * @fires A11yAutocomplete#autocomplete-change
 * @fires A11yAutocomplete#autocomplete-close
 * @fires A11yAutocomplete#autocomplete-created
 * @fires A11yAutocomplete#autocomplete-destroy
 * @fires A11yAutocomplete#autocomplete-highlight
 * @fires A11yAutocomplete#autocomplete-open
 * @fires A11yAutocomplete#autocomplete-pre-search
 * @fires A11yAutocomplete#autocomplete-response
 * @fires A11yAutocomplete#autocomplete-select
 */
const A11yAutocomplete = (input, options = {}) => {
  const autocomplete = new _A11yAutocomplete(input, options);
  return autocomplete.api;
};

export default A11yAutocomplete;
