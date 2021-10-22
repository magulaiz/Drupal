import keys from './keys.js';

/**
 * This represents the listbox and handles all keyboard and mouse events
 * regarding navigating and selecting an option from a given list.
 *
 * When a value is selected, the index from the list of suggestion to display
 * is returned.
 */
class Listbox {
  constructor(options, parentOptions) {
    this.keyCode = keys;
    this.options = parentOptions;
    this.notify = options.notify;
    // Create the list that will display suggestions.
    this.ul = document.createElement('ul');
    this.ul.setAttribute('id', options.id);
    this.implementList();
    this.announceTimeOutId = null;
    this.suggestions = [];

    // Events to add.
    this.events = {
      ul: {
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
  }

  /**
   * Sets attributes to the results list and inserts it in the DOM.
   */
  implementList() {
    this.ul.setAttribute('role', 'listbox');
    this.ul.setAttribute('data-autocomplete-item-list', '');
    this.ul.setAttribute('hidden', '');
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

  itemIndex(item) {
    return +item.dataset.itemIndex;
  }

  itemsLength() {
    return this.suggestions.length;
  }

  /**
   * Handles keydown events on the item list.
   *
   * @param {Event} e
   *   The keydown event.
   */
  listKeyDown(e) {
    if (
      !this.ul.contains(document.activeElement) ||
      e.ctrlKey ||
      e.altKey ||
      e.metaKey
    ) {
      return;
    }

    let changeSelection = false;
    const focused = e.target.closest('[role="option"]');
    const index = this.itemIndex(focused);
    const length = this.itemsLength();

    switch (e.keyCode) {
      case this.keyCode.SPACE:
      case this.keyCode.RETURN:
        this.selectItem(index);
        this.close();
        break;

      case this.keyCode.ESC:
      case this.keyCode.TAB:
        this.close();
        break;

      case this.keyCode.UP:
        if (index > 0) {
          changeSelection = true;
          this.focusItem(index - 1);
        }
        // Send the focus back to the combobox.
        if (index === 0) {
          this.notify('focusout');
        }
        break;

      case this.keyCode.DOWN:
        if (index + 1 < length) {
          changeSelection = true;
          this.focusItem(index + 1);
        }
        break;

      case this.keyCode.HOME:
        changeSelection = true;
        this.focusItem(0);
        break;

      case this.keyCode.END:
        changeSelection = true;
        this.focusItem(length - 1);
        break;

      default:
        break;
    }

    if (changeSelection) {
      focused.setAttribute('aria-selected', false);
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

  focusItem(index) {
    const item = this.ul.querySelector(
      `[role="option"][data-item-index="${index}"]`,
    );
    if (item) {
      this.preventCloseOnBlur = true;
      this.highlightItem(item);
    } else {
      this.close();
    }
  }

  /**
   * Highlights and focuses a selected item.
   *
   * @param {HTMLElement|null} item
   *   The list item being selected.
   */
  highlightItem(item = null) {
    if (!item) {
      item = this.ul.querySelector('[role="option"]');
    }
    item.setAttribute('aria-selected', true);
    item.focus();

    const selectedIndex = this.itemIndex(item);
    this.notify('highlight', {
      selectedIndex,
      posinset: selectedIndex + 1,
      setsize: this.itemsLength(),
    });
  }

  /**
   * Handles click events on the item list.
   *
   * @param {Event} e
   *   The click event.
   */
  itemClick(e) {
    if (e.button === 0) {
      const focused = e.target.closest('[role="option"]');
      if (focused) {
        this.selectItem(this.itemIndex(focused));
      }
    }
  }

  /**
   * Selects an item in the autocomplete list.
   *
   * @param {int} selectedIndex
   *  The element containing the item
   */
  selectItem(selectedIndex) {
    this.notify('select', { selectedIndex });
    this.close();
  }

  /**
   * Creates a list item that displays the suggestion.
   *
   * @param {string} suggestion
   *   A suggestion based on user input. It is an object with label and value
   *   properties.
   * @param {int} index
   *
   * @return {HTMLElement}
   *   A list item with the suggestion.
   */
  suggestionItem(suggestion, index) {
    const li = document.createElement('li');
    li.innerHTML = suggestion;
    li.setAttribute('role', 'option');
    li.setAttribute('tabindex', '-1');
    li.setAttribute('aria-selected', 'false');
    li.setAttribute('data-item-index', index);
    return li;
  }

  /**
   *
   * @param {string[]} results
   */
  displayResults(results) {
    this.ul.innerHTML = '';
    this.suggestions = results;
    const fragment = document.createDocumentFragment();
    const appendToFragment = fragment.appendChild.bind(fragment);
    results.map(this.suggestionItem).forEach(appendToFragment);
    this.ul.appendChild(fragment);
  }

  open() {
    this.ul.removeAttribute('hidden');
    this.ul.style.zIndex = this.options.listZindex;
    if (this.options.autoFocus) {
      this.highlightItem();
    }
  }

  close() {
    this.ul.setAttribute('hidden', '');
    this.ul.innerHTML = '';
    this.suggestions = [];
    this.notify('focusout');
  }

  destroy() {
    this.ul.parentNode.removeChild(this.ul);
  }
}

export default Listbox;
