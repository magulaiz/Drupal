import keys from './keys.js';
import Listbox from './listbox.js';

/**
 * This class handles the input events and formatting of the suggestion items to
 * display.
 */
class Combobox {
  constructor(input, options) {
    this.keyCode = keys;
    this.options = options;

    this.originalInput = input.cloneNode();
    this.input = input;

    this.count = document.querySelectorAll('[data-autocomplete-input]').length;
    this.listboxId = `autocomplete-listbox-${this.count}`;
    this.input.setAttribute('aria-owns', `${this.listboxId}-suggestions`);

    // Create a div that will wrap the input and suggestion list.
    this.wrapper = document.createElement('div');
    this.implementWrapper();
    // When applicable, create a live region for announcing suggestion results
    // to assistive technology.
    this.liveRegion = null;
    this.implementLiveRegion();

    this.listbox = new Listbox(
      {
        id: input.getAttribute('aria-owns'),
        notify: this.handleEvent.bind(this),
      },
      options,
    );
    this.inputDescribedBy = this.input.getAttribute('aria-describedby');
    this.inputHintRead = false;
    this.implementInput();
    this.implementDescription();
    this.preventCloseOnBlur = false;
    this.isOpened = false;

    // Events to add.
    this.events = {
      input: {
        blur: (e) => this.blurHandler(e),
        keydown: (e) => this.inputKeyDown(e),
      },
      wrapper: {
        keydown: (e) => this.wrapperKeyDown(e),
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

    this.appendList();
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
    this.input.setAttribute('role', 'combobox');
    this.input.setAttribute('aria-expanded', 'false');
    if (!this.input.hasAttribute('id')) {
      this.input.setAttribute('id', `autocomplete-input-${this.count}`);
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
    this.wrapper.appendChild(this.listbox.ul);
  }

  handleEvent(type, detail) {
    switch (type) {
      case 'select':
        this.handleSelect(detail);
        break;

      case 'highlight':
        this.handleHighlight(detail);
        break;

      case 'focusout':
        this.input.focus();
        break;
    }
  }

  handleSelect({ selectedIndex }) {
    this.close();
    this.input.focus();
    const value = this.suggestions[selectedIndex];
    const event = this.input.dispatchEvent(
      new CustomEvent('option-selected', {
        bubbles: true,
        cancelable: true,
        detail: { value },
      }),
    );
    // The event can be cancelled when the input value is manipulated somewhere
    // else.
    if (event) {
      // todo add the template function.
      this.input.value = value.value;
    }
  }

  handleHighlight({ selectedIndex, posinset, setsize }) {
    this.announce(
      this.options.highlightedAssistiveHint
        .replace('@selectedItem', this.suggestions[selectedIndex].value)
        .replace('@position', posinset)
        .replace('@count', setsize),
    );
  }

  /**
   * Announces to assistive tech when an item is highlighted.
   *
   * @param {string} message
   *   The list item being selected.
   */
  announce(message) {
    window.clearTimeout(this.announceTimeOutId);
    // Delay the announcement by 500 milliseconds. This prevents unnecessary
    // calls when a user is navigating quickly.
    this.announceTimeOutId = setTimeout(
      () => this.sendToLiveRegion(message),
      500,
    );
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
      this.close();
    }
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
        this.listbox.highlightItem();
      }
    }
    this.removeAssistiveHint();
  }

  wrapperKeyDown(e) {
    const { keyCode } = e;
    if (keyCode === this.keyCode.ESC) {
      this.close();
      this.input.focus();
    }
  }

  /**
   * Displays the results retrieved in inputListener().
   */
  displayResults(results) {
    this.suggestions = results;
    this.listbox.displayResults(
      results.map((item) => this.formatSuggestionItem(item)),
    );
    this.open();

    window.clearTimeout(this.announceTimeOutId);
    // Delay the results announcement by 1400 milliseconds. This prevents
    // unnecessary calls when a user is typing quickly, and avoids the results
    // announcement being cut short by the screenreader stating the just-typed
    // character.
    this.announceTimeOutId = setTimeout(
      () => this.sendToLiveRegion(this.resultsMessage(results.length)),
      1400,
    );
  }

  /**
   * Formats how a suggestion is structured in the suggestion list.
   *
   * @param {object} suggestion
   *   Object with value and label properties.
   * @param {Element} li
   *   The list element.
   *
   * @return {string}
   *   The text and html of a suggestion item.
   */
  // eslint-disable-next-line no-unused-vars
  formatSuggestionItem(suggestion, li) {
    const propertyToDisplay = this.options.displayLabels ? 'label' : 'value';
    return suggestion[propertyToDisplay].trim();
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
   * Opens the suggestion list.
   */
  open() {
    this.input.setAttribute('aria-expanded', 'true');
    this.isOpened = true;
    this.listbox.ul.style.minWidth = `${this.input.offsetWidth - 4}px`;
    this.listbox.open();
    if (this.options.autoFocus) {
      this.preventCloseOnBlur = true;
    }
  }

  /**
   * Closes the suggestion list.
   */
  close() {
    window.clearTimeout(this.announceTimeOutId);
    if (this.isOpened) {
      this.input.setAttribute('aria-expanded', 'false');
      this.isOpened = false;
      this.listbox.close();
    }
  }

  destroy() {
    Object.keys(this.events).forEach((elementName) => {
      Object.keys(this.events[elementName]).forEach((eventName) => {
        this[elementName].removeEventListener(
          eventName,
          this.events[elementName][eventName],
        );
      });
    });
    this.listbox.destroy();
    this.wrapper.parentNode.replaceChild(this.originalInput, this.wrapper);
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
}

export default Combobox;
