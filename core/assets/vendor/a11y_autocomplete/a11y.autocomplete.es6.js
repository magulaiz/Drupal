/*! @drupal/autocomplete - v0.0.0 - 2021-10-29 */
/**
 * @module @drupal/autocomplete
 *
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
 *     <!-- This is the <listboxWrapper> that will list the query results when characters are typed in the input. -->
 *     <listboxWrapper role="listbox" data-drupal-autocomplete-list="" id="autocomplete-listbox-0" hidden=""></listboxWrapper>
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
 * @property {Array|string|A11yAutocomplete~sourceCallback} [source] -  The data
 * the autocomplete searches, which can be provided in multiple ways
 * - An array of strings: `[ "First choice", "Second Choice", ... ]`
 * - An array of objects. All objects must have either a 'label' or 'value'
 *   property: `[ { label: "First Choice", value: "First Value" }, ... ]`
 *   The label property is displayed in the suggestion list, and is what the
 *   autocomplete process searches. The value property is what is inserted into
 *   the input element when an item is selected. If just one property (value or
 *   label) is specified, it will be used for both, e.g., if only 'value' is
 *   provided, it will also be used as the label.
 *   Note that these objects can have additional properties beyond 'label' and
 *   'value', but at least one of those two properties must be present for the
 *   autocomplete to work.
 *  - A string of valid JSON that can be parsed into an array of strings or an
 *    array of objects (i.e. either of the array options listed above).
 * - A callback function with two arguments:
 *     - searchTerm: the string the autocomplete is searching for.
 *     - processResults: a callback function that sends the search results
 *       to for rendering. This function accepts a single argument that should
 *       either be an array of strings or an array of objects with value and/or
 *       label properties.
 *  ```
 *    // Example querying an endpoint that provides JSON.
 *    source: (searchTerm, processResults) => {
 *        // Query the remote source
 *        fetch(`https://an-endpoint/?q=${searchTerm}`)
 *           .then((response) => {
 *             return response.json();
 *           })
 *           .then((results) => {
 *             // Use the callback to send the results to the
 *             processResults(results);
 *           });
 *    },
 *  ```
 * @property {Object} [templates] - Defines templates (functions) that are used
 *  for displaying parts of the autocomplete.
 * @property {A11yAutocomplete~suggestionTemplate} templates.suggestions -
 *  Defines template for suggestion items.
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
   * @param {HTMLInputElement} input
   *   The element to be used as an autocomplete.
   * @param {A11yAutocomplete~Options} options
   *  Autocomplete options.
   */
  constructor(input, options = {}) {
    this.keyCode = Object.freeze({
      TAB: 9,
      RETURN: 13,
      ESC: 27,
      SPACE: 32,
      PAGEUP: 33,
      PAGEDOWN: 34,
      END: 35,
      HOME: 36,
      LEFT: 37,
      UP: 38,
      RIGHT: 39,
      DOWN: 40,
    });

    this.originalInput = input.cloneNode();
    this.input = input;

    this.count = document.querySelectorAll('[data-autocomplete-input]').length;
    this.listboxId = `autocomplete-l${this.count}`;

    this.supportedOptions = [
      'source',
      'cardinality',
      'minChars',
      'separatorChar',
      'firstCharacterIgnoreList',
      'createLiveRegion',
      'autoFocus',
      'allowRepeatValues',
      'groupBy',
      'templates',
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
      disabled: false,
      source: [],
      groupBy: null,
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
      templates: {
        /**
         * Formats how a suggestion is structured in the suggestion list.
         *
         * @callback A11yAutocomplete~suggestionTemplate
         *
         * @param {object} suggestion
         *   Object with value and label properties.
         *
         * @return {string}
         *   The text and html of a suggestion item.
         */
        // eslint-disable-next-line no-unused-vars
        suggestion: (suggestion) => {
          return suggestion.label.trim();
        },
      },
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

    this.selected = null;
    this.preventCloseOnBlur = false;
    this.isOpened = false;
    this.suggestions = [];
    this.announceTimeOutId = null;
    this.searchTimeOutId = null;

    // Create a div that will wrap the input and suggestion list.
    this.wrapper = document.createElement('div');
    this.implementWrapper();

    this.inputDescribedBy = this.input.getAttribute('aria-describedby');
    this.inputHintRead = false;
    this.implementInput();
    this.implementDescription();

    // Create the list that will display suggestions.
    this.listboxWrapper = document.createElement(this.options.groupBy ? 'div' : 'ul');
    this.implementList();
    this.appendList();

    // When applicable, create a live region for announcing suggestion results
    // to assistive technology.
    this.liveRegion = null;
    this.implementLiveRegion();

    // Events to add.
    this.events = {
      input: {
        input: (e) => this.inputListener(e),
        blur: (e) => this.blurHandler(e),
        keydown: (e) => this.inputKeyDown(e),
      },
      listboxWrapper: {
        mousedown: (e) => e.preventDefault(),
        click: (e) => this.itemClick(e),
        keydown: (e) => this.listKeyDown(e),
        blur: (e) => this.blurHandler(e),
        focus: (e) => this.listFocus(e),
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
   * Sets attributes to the wrapper and inserts it in the DOM.
   */
  implementWrapper() {
    this.wrapper.setAttribute('data-autocomplete-wrapper', '');
    this.input.parentNode.appendChild(this.wrapper);
    this.wrapper.appendChild(this.input);
  }

  /**
   * Sets attributes to the input and inserts it in the DOM.
   */
  implementInput() {
    // Add attributes to the input.
    this.input.setAttribute('aria-autocomplete', 'list');
    this.input.setAttribute('autocomplete', 'off');
    this.input.setAttribute('data-autocomplete-input', '');
    this.input.setAttribute('aria-owns', this.listboxId);
    this.input.setAttribute('role', 'combobox');
    this.input.setAttribute('aria-expanded', 'false');
    if (this.options.inputClass.length > 0) {
      // should have a helper method for this and explain it's because of IE
      // we do the forEach. Also it fails when the class is an empty string.
      this.options.inputClass
        .split(' ')
        .forEach((className) => this.input.classList.add(className));
    }
    if (!this.input.hasAttribute('id')) {
      this.input.setAttribute('id', `autocomplete-input-${this.count}`);
    }
  }

  /**
   * Adds assistive hints.
   */
  implementDescription() {
    const description = document.createElement('span');
    description.textContent =
      this.minCharsMessage() + this.options.inputAssistiveHint;
    description.classList.add('visually-hidden');

    // If the autocomplete input has an pre-existing 'aria-describedby', append
    // autocomplete-specific descriptions to that existing element. Otherwise,
    // create a new element with those descriptions and create a new
    // 'aria-describedby' to associate it with the input.
    if (this.inputDescribedBy) {
      // This is content that is appended to an existing description. It's also
      // content that only needs to be conveyed once. Add an attribute that
      // allows this content to be targeted for removal after it is read once.
      description.setAttribute(
        'data-autocomplete-assistive-hint',
        `${this.count}`,
      );
      document
        .querySelector(`[id="${this.inputDescribedBy}"]`)
        .appendChild(description);
    } else {
      description.setAttribute('id', `assistive-hint-${this.count}`);
      this.input.setAttribute(
        'aria-describedby',
        `assistive-hint-${this.count}`,
      );
      this.wrapper.appendChild(description);
    }
  }

  /**
   * Inserts list into DOM.
   */
  appendList() {
    this.input.parentNode.appendChild(this.listboxWrapper);
  }

  /**
   * Sets attributes to the results list and inserts it in the DOM.
   */
  implementList() {
    this.listboxWrapper.setAttribute('role', 'listbox');
    this.listboxWrapper.setAttribute('data-autocomplete-item-list', '');
    this.listboxWrapper.setAttribute('id', this.listboxId);
    this.listboxWrapper.setAttribute('hidden', '');
    if (this.options.ulClass.length > 0) {
      this.options.ulClass
        .split(' ')
        .forEach((className) => this.listboxWrapper.classList.add(className));
    }
  }

  /**
   * Creates a live region for reporting status to assistive technology.
   */
  implementLiveRegion() {
    // If the liveRegion option is set to true, create a new live region and
    // insert it in the autocomplete wrapper.
    if (this.options.createLiveRegion === true) {
      this.liveRegion = document.createElement('span');
      this.liveRegion.setAttribute('data-autocomplete-live-region', '');
      this.liveRegion.setAttribute('aria-live', 'assertive');
      this.input.parentNode.appendChild(this.liveRegion);
    }

    // If the liveRegion option is a string, it should be a selector for an
    // already-existing live region.
    if (typeof this.options.liveRegion === 'string') {
      this.liveRegion = document.querySelector(this.options.liveRegion);
    }
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
   * @param {Event} e
   *   The blur event.
   */
  blurHandler(e) {
    // If an element is blurred, cancel any pending screenreader announcements
    // as they would be specific to an element no longer in focus.
    window.clearTimeout(this.announceTimeOutId);
    if (this.preventCloseOnBlur) {
      this.preventCloseOnBlur = false;
      e.preventDefault();
    } else {
      /**
       * Fires after an item is blurred and another item hasn't been highlighted.
       *
       * @event A11yAutocomplete#autocomplete-change
       * @property {Class} autocomplete - The autocomplete instance.
       */
      this.triggerEvent('autocomplete-change');
      this.close();
    }
  }

  /**
   * Removes one-time-only assistive hints.
   */
  removeAssistiveHint() {
    if (!this.inputHintRead) {
      if (this.inputDescribedBy) {
        const appendedHint = document.querySelector(
          `[data-autocomplete-assistive-hint="${this.count}"]`,
        );
        appendedHint.parentNode.removeChild(appendedHint);
      } else {
        this.input.removeAttribute('aria-describedby');
      }
      this.inputHintRead = true;
    }
  }

  /**
   * Handles keydown events on the item list.
   *
   * @param {Event} e
   *   The keydown event.
   */
  listKeyDown(e) {
    if (
      !this.listboxWrapper.contains(document.activeElement) ||
      e.ctrlKey ||
      e.altKey ||
      e.metaKey ||
      e.keyCode === this.keyCode.TAB
    ) {
      return;
    }

    this.listboxWrapper.querySelectorAll('[aria-selected="true"]').forEach((li) => {
      li.setAttribute('aria-selected', 'false');
    });

    switch (e.keyCode) {
      case this.keyCode.SPACE:
      case this.keyCode.RETURN:
        this.selectItem(document.activeElement, e);
        this.close();
        this.input.focus();
        break;

      case this.keyCode.ESC:
      case this.keyCode.TAB:
        this.input.focus();
        this.close();
        break;

      case this.keyCode.UP:
        this.focusPrev();
        break;

      case this.keyCode.DOWN:
        this.focusNext();
        break;
    }

    e.stopPropagation();
    e.preventDefault();
  }

  /**
   * Handles focus events on the item list.
   *
   * @param {Event} e
   *   The focus event.
   */
  // eslint-disable-next-line no-unused-vars, class-methods-use-this
  listFocus(e) {
    // Intentionally empty, can be overridden.
  }

  /**
   * Moves focus to the previous list item.
   */
  focusPrev() {
    this.preventCloseOnBlur = true;
    const currentItem = document.activeElement.getAttribute(
      'data-autocomplete-item',
    );
    const prevIndex = parseInt(currentItem, 10) - 1;
    const previousItem = this.listboxWrapper.querySelector(
      `[data-autocomplete-item="${prevIndex}"]`,
    );

    if (previousItem) {
      this.highlightItem(previousItem);
    } else {
      this.input.focus();
    }
  }

  /**
   * Moves focus to the next list item.
   */
  focusNext() {
    const currentItem = document.activeElement.getAttribute(
      'data-autocomplete-item',
    );
    const nextIndex = parseInt(currentItem, 10) + 1;
    const nextItem = this.listboxWrapper.querySelector(
      `[data-autocomplete-item="${nextIndex}"]`,
    );
    if (nextItem) {
      this.preventCloseOnBlur = true;
      this.highlightItem(nextItem);
    }
  }

  /**
   * Highlights and focuses a selected item.
   *
   * @param {HTMLElement} item
   *   The list item being selected.
   */
  highlightItem(item) {
    item.setAttribute('aria-selected', true);
    item.focus();
    const itemIndex = item
      .closest('[data-autocomplete-item]')
      .getAttribute('data-autocomplete-item');

    /**
     * Fires when an item is highlighted.
     *
     * @event A11yAutocomplete#autocomplete-highlight
     * @property {Class} autocomplete - The autocomplete instance.
     * @property {Object} selected - the currently selected item,
     *   as an object with 'label' and 'value' properties.
     */
    this.triggerEvent('autocomplete-highlight', {
      selected: this.suggestions[itemIndex],
    });
    this.announceHighlight(item);
  }

  /**
   * Announces to assistive tech when an item is highlighted.
   *
   * @param {HTMLElement} item
   *   The list item being selected.
   */
  announceHighlight(item) {
    window.clearTimeout(this.announceTimeOutId);
    // Delay the announcement by 500 milliseconds. This prevents unnecessary
    // calls when a user is navigating quickly.
    this.announceTimeOutId = setTimeout(
      () => this.sendToLiveRegion(this.highlightMessage(item)),
      500,
    );
  }

  /**
   * A message announced when an item is highlighted.
   *
   * @param {HTMLElement} item
   *  The list item being highlighted.
   * @return {string}
   *  The message conveying that the item is highlighted
   */
  highlightMessage(item) {
    const itemIndex = item
      .closest('[data-autocomplete-item]')
      .getAttribute('data-autocomplete-item');
    const selectedItem = this.suggestions[itemIndex].value;
    return this.options.highlightedAssistiveHint
      .replace('@selectedItem', selectedItem)
      .replace('@position', item.getAttribute('aria-posinset'))
      .replace('@count', this.listboxWrapper.children.length);
  }

  /**
   * Handles keydown events on the autocomplete input.
   *
   * @param {Event} e
   *   The keydown event.
   */
  inputKeyDown(e) {
    const { keyCode } = e;
    if (this.isOpened) {
      if (keyCode === this.keyCode.ESC) {
        this.close();
      }
      if (keyCode === this.keyCode.DOWN) {
        e.preventDefault();
        this.preventCloseOnBlur = true;
        this.highlightItem(this.listboxWrapper.querySelector('[role="option"]'));
      }
    }
    this.removeAssistiveHint();
  }

  /**
   * Handles click events on the item list.
   *
   * @param {Event} e
   *   The click event.
   */
  itemClick(e) {
    const li = e.target;

    if (li && e.button === 0) {
      this.selectItem(li, e);
    }
  }

  /**
   * Selects an item in the autocomplete list.
   *
   * @param {Element} elementWithItem
   *  The element containing the item
   * @param {Event} e
   *  The event that triggered te selection.
   */
  selectItem(elementWithItem, e) {
    const itemIndex = elementWithItem
      .closest('[data-autocomplete-item]')
      .getAttribute('data-autocomplete-item');
    const toSelect = this.suggestions[itemIndex];

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
        selected: toSelect,
      },
      true,
      e,
    );
    if (selected) {
      this.replaceInputValue(elementWithItem);
      e.preventDefault();
      this.close();
      /**
       * Fires after an item is added to the autocomplete
       *
       * @event A11yAutocomplete#autocomplete-selection-added
       * @property {Class} autocomplete - The autocomplete instance.
       * @property {string} added - the text added to the input value.
       */
      this.triggerEvent('autocomplete-selection-added', {
        added: elementWithItem.textContent,
      });
    }
  }

  /**
   * Replaces the value of an input field when a new value is chosen.
   *
   * @param {Element} element
   *   The element with the item to be added.
   */
  replaceInputValue(element) {
    const itemIndex = element
      .closest('[data-autocomplete-item]')
      .getAttribute('data-autocomplete-item');
    this.selected = this.suggestions[itemIndex];
    const separator = this.separator();
    if (separator.length > 0) {
      const before = this.previousItems(separator);
      this.input.value = `${before}${this.selected.value}`;
    } else {
      this.input.value = this.selected.value;
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
    return numItems < parseInt(cardinality, 10) ||
      parseInt(cardinality, 10) <= 0
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
      this.close();
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
          this.displayResults(results);
        });
      } else if (Array.isArray(this.options.source)) {
        // If a predefined list was provided as an option, make this the
        // suggestion items.
        this.displayResults(
          this.options.source.filter((item) =>
            this.filterResults(item, searchTerm),
          ),
        );
      } else {
        throw new TypeError('options.source is not an array or a function.');
      }
    } else {
      // If the search query is empty, provide an empty list of suggestions.
      this.displayResults([]);
    }
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
   * Converts all suggestions into an object with value and label properties.
   *
   * @param {Array} suggestionItems
   *   An array of suggestion items to be normalized.
   * @return {Array}
   *   An array of normalized suggestion items.
   */
  normalizeSuggestionItems(suggestionItems) {
    return suggestionItems.map((item) => {
      if (typeof item === 'string') {
        return { value: item, label: item };
      }

      return {
        ...item,
        value: item.value || item.label || item,
        label: item.label || item.value || item,
      };
    });
  }

  /**
   * Displays the results retrieved in inputListener().
   */
  displayResults(suggestionItems) {
    this.suggestions = this.normalizeSuggestionItems(suggestionItems);
    this.listboxWrapper.innerHTML = '';
    /**
     * Fires after suggestion items are retrieved, but before they are added to the DOM.
     *
     * @event A11yAutocomplete#autocomplete-response
     * @property {Class} autocomplete - The autocomplete instance.
     * @property {Object[]} list - an array of suggestions as objects with 'label'
     *  and 'value' properties.
     */
    this.triggerEvent('autocomplete-response', {
      list: this.suggestions,
    });
    if (this.suggestions.length) {
      if (this.options.sort !== false) {
        this.suggestions = this.sortSuggestions(this.suggestions);
      }
      let list;
      const fragment = document.createDocumentFragment();
      const appendToFragment = fragment.appendChild.bind(fragment);
      const suggestionItem = this.suggestionItem.bind(this);
      const {
        groupBy,
        templates: { suggestion: formatItem },
      } = this.options;
      if (groupBy) {
        let index = 0;
        let groupId = 0;
        list = {};
        // Group suggestions and transform them to text.
        const groups = this.suggestions.reduce(this.groupResultsCallback(groupBy), {});
        Object.keys(groups).forEach((group) => {
          list[group] = groups[group].map(formatItem);
        });

        // Append the list of suggestions
        Object.keys(list)
          .map((group) => {
            const el = this.suggestionGroup(group, `${this.listboxId}-g${groupId++}`);
            const appendToGroup = el.appendChild.bind(el);
            list[group]
              .map((suggestionText) =>
                suggestionItem(suggestionText, index++),
              )
              .forEach(appendToGroup);
            return el;
          })
          .forEach(appendToFragment);
      } else {
        list = this.suggestions.map(formatItem);
        list.map(suggestionItem).forEach(appendToFragment);
      }

      this.listboxWrapper.appendChild(fragment);
    }
    if (this.suggestions.length === 0) {
      this.close();
    } else {
      this.open();
    }

    window.clearTimeout(this.announceTimeOutId);
    // Delay the results announcement by 1400 milliseconds. This prevents
    // unnecessary calls when a user is typing quickly, and avoids the results
    // announcement being cut short by the screenreader stating the just-typed
    // character.
    this.announceTimeOutId = setTimeout(
      () => this.sendToLiveRegion(this.resultsMessage(this.suggestions.length)),
      1400,
    );
  }

  /**
   * Sorts the array of suggestions.
   */
  sortSuggestions(suggestionItems) {
    return suggestionItems.sort((prior, current) =>
      prior.label.toUpperCase() > current.label.toUpperCase() ? 1 : -1,
    );
  }

  /**
   *
   * @param groupBy
   * @returns {(function(*, *=): (*))|*}
   */
  groupResultsCallback(groupBy) {
    return (groups, item) => {
      const groupLabel = item[groupBy];
      // Items without group key are not rendered.
      if (!groupLabel) {
        return groups;
      }
      if (!groups[groupLabel]) {
        groups[groupLabel] = [];
      }
      groups[groupLabel].push(item);
      return groups;
    };
  }

  /**
   * Creates a suggestion group.
   *
   * @param {string} group
   *   A string containing HTML or text for the label of the group.
   * @param {string} id
   *   A unique ID for the group.
   *
   * @returns {HTMLElement}
   *   A list representing a group.
   */
  suggestionGroup(group, id) {
    const ul = document.createElement('ul');
    ul.setAttribute('role', 'group');
    ul.setAttribute('aria-labelledby', id);
    const li = document.createElement('li');
    li.innerHTML = group;
    li.setAttribute('role', 'presentation');
    li.setAttribute('id', id);
    ul.appendChild(li);
    return ul;
  }

  /**
   * Creates a list item that displays the suggestion.
   *
   * @param {string} suggestion
   *   A suggestion based on user input. It is an object with label and value
   *   properties.
   * @param {number} itemIndex
   *   The index of the item.
   *
   * @return {HTMLElement}
   *   A list item with the suggestion.
   */
  suggestionItem(suggestion, itemIndex) {
    const li = document.createElement('li');
    li.innerHTML = suggestion;
    li.setAttribute('role', 'option');
    li.setAttribute('tabindex', '-1');
    li.setAttribute('id', `${this.listboxId}-i${itemIndex}`);
    li.setAttribute('data-autocomplete-item', itemIndex);
    li.setAttribute('aria-posinset', itemIndex + 1);
    li.setAttribute('aria-selected', 'false');
    li.onblur = (e) => this.blurHandler(e);

    return li;
  }

  /**
   * Opens the suggestion list.
   */
  open() {
    this.input.setAttribute('aria-expanded', 'true');
    this.listboxWrapper.removeAttribute('hidden');
    this.listboxWrapper.style.zIndex = this.options.listZindex;
    this.isOpened = true;
    this.listboxWrapper.style.minWidth = `${this.input.offsetWidth - 4}px`;

    /**
     * Fires after the suggestion list opens.
     *
     * @event A11yAutocomplete#autocomplete-open
     * @property {Class} autocomplete - The autocomplete instance.
     * // should add the result list in the event maybe?
     */
    this.triggerEvent('autocomplete-open');
    if (this.options.autoFocus) {
      this.preventCloseOnBlur = true;
      this.highlightItem(this.listboxWrapper.querySelector('[data-autocomplete-item="0"]'));
    }
  }

  /**
   * Closes the suggestion list.
   */
  close() {
    if (this.isOpened) {
      this.input.setAttribute('aria-expanded', 'false');
      this.listboxWrapper.setAttribute('hidden', '');
      this.isOpened = false;

      /**
       * Fires after the suggestion list closes.
       *
       * @event A11yAutocomplete#autocomplete-close
       * @property {Class} autocomplete - The autocomplete instance.
       */
      this.triggerEvent('autocomplete-close');
    }
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
    const suggestionLabel = suggestion.label || suggestion.value || suggestion;
    const currentValues = this.splitValues();

    // Prevent suggestions if the first input character is in the ignore list,
    // if the suggestion has already been added to the field, or if the maximum
    // number of items have been reached.
    if (
      firstCharacterIgnoreList.indexOf(typed[0]) !== -1 ||
      (cardinality > 0 && currentValues.length > cardinality) ||
      (currentValues.indexOf(suggestionLabel) !== -1 &&
        !this.options.allowRepeatValues)
    ) {
      return false;
    }

    return RegExp(
      this.extractLastInputValue()
        .trim()
        .replace(/[-\\^$*+?.()|[\]{}]/g, '\\$&'),
      'i',
    ).test(suggestionLabel);
  }

  /**
   * Sends a message to the configured live region.
   *
   * @param {string} message
   *   The message to be sent to the live region.
   */
  sendToLiveRegion(message) {
    if (this.liveRegion) {
      this.liveRegion.textContent = message;
    }
  }

  /**
   * A message regarding the number of suggestions found.
   *
   * @param {number} count
   *   The number of suggestions found.
   *
   * @return {string}
   *   A message based on the number of suggestions found.
   */
  resultsMessage(count) {
    let message;
    if (count === 0) {
      message = this.options.noResultsAssistiveHint;
    } else if (count === 1) {
      message = this.options.oneResultAssistiveHint;
    } else {
      message = this.options.someResultsAssistiveHint;
    }

    return message.replace('@count', count);
  }

  /**
   * A message stating the number of characters needed to trigger autocomplete.
   *
   * @return {string}
   *  The minimum characters message.
   */
  minCharsMessage() {
    if (this.options.minChars > 1) {
      return `${this.options.minCharAssistiveHint.replace(
        '@count',
        this.options.minChars,
      )}. `;
    }
    return '';
  }

  /**
   * Remove all event listeners added by this class.
   * @callback A11yAutocomplete~destroy
   */
  destroy() {
    Object.keys(this.events).forEach((elementName) => {
      Object.keys(this.events[elementName]).forEach((eventName) => {
        this[elementName].removeEventListener(
          eventName,
          this.events[elementName][eventName],
        );
      });
    });
    if (this.wrapper && this.wrapper.parentNode) {
      this.wrapper.parentNode.replaceChild(this.originalInput, this.wrapper);
    }

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
 * @fires A11yAutocomplete#autocomplete-selection-added
 */
const A11yAutocompleteFactory = (input, options = {}) => {
  const autocomplete = new _A11yAutocomplete(input, options);
  return autocomplete.api;
};

window.A11yAutocomplete = A11yAutocompleteFactory;
