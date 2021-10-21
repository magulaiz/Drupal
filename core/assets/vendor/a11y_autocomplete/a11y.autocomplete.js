/*! @drupal/autocomplete - v0.0.0 - 2021-10-21 */
function ownKeys(object, enumerableOnly) {
  var keys = Object.keys(object);

  if (Object.getOwnPropertySymbols) {
    var symbols = Object.getOwnPropertySymbols(object);

    if (enumerableOnly) {
      symbols = symbols.filter(function (sym) {
        return Object.getOwnPropertyDescriptor(object, sym).enumerable;
      });
    }

    keys.push.apply(keys, symbols);
  }

  return keys;
}

function _objectSpread2(target) {
  for (var i = 1; i < arguments.length; i++) {
    var source = arguments[i] != null ? arguments[i] : {};

    if (i % 2) {
      ownKeys(Object(source), true).forEach(function (key) {
        _defineProperty(target, key, source[key]);
      });
    } else if (Object.getOwnPropertyDescriptors) {
      Object.defineProperties(target, Object.getOwnPropertyDescriptors(source));
    } else {
      ownKeys(Object(source)).forEach(function (key) {
        Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key));
      });
    }
  }

  return target;
}

function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}

function _defineProperty(obj, key, value) {
  if (key in obj) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
  } else {
    obj[key] = value;
  }

  return obj;
}

function _unsupportedIterableToArray(o, minLen) {
  if (!o) return;
  if (typeof o === "string") return _arrayLikeToArray(o, minLen);
  var n = Object.prototype.toString.call(o).slice(8, -1);
  if (n === "Object" && o.constructor) n = o.constructor.name;
  if (n === "Map" || n === "Set") return Array.from(o);
  if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen);
}

function _arrayLikeToArray(arr, len) {
  if (len == null || len > arr.length) len = arr.length;

  for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i];

  return arr2;
}

function _createForOfIteratorHelper(o, allowArrayLike) {
  var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"];

  if (!it) {
    if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") {
      if (it) o = it;
      var i = 0;

      var F = function () {};

      return {
        s: F,
        n: function () {
          if (i >= o.length) return {
            done: true
          };
          return {
            done: false,
            value: o[i++]
          };
        },
        e: function (e) {
          throw e;
        },
        f: F
      };
    }

    throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
  }

  var normalCompletion = true,
      didErr = false,
      err;
  return {
    s: function () {
      it = it.call(o);
    },
    n: function () {
      var step = it.next();
      normalCompletion = step.done;
      return step;
    },
    e: function (e) {
      didErr = true;
      err = e;
    },
    f: function () {
      try {
        if (!normalCompletion && it.return != null) it.return();
      } finally {
        if (didErr) throw err;
      }
    }
  };
}

var keys = Object.freeze({
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
  DOWN: 40
});

/**
 * This represents the listbox and handles all keyboard and mouse events
 * regarding navigating and selecting an option from a given list.
 *
 * When a value is selected, the index from the list of suggestion to display
 * is returned.
 */

var Listbox = /*#__PURE__*/function () {
  function Listbox(options, parentOptions) {
    var _this = this;

    _classCallCheck(this, Listbox);

    this.keyCode = keys;
    this.options = parentOptions;
    this.notify = options.notify; // Create the list that will display suggestions.

    this.ul = document.createElement('ul');
    this.ul.setAttribute('id', options.id);
    this.implementList();
    this.announceTimeOutId = null;
    this.suggestions = []; // Events to add.

    this.events = {
      ul: {
        mousedown: function mousedown(e) {
          return e.preventDefault();
        },
        click: function click(e) {
          return _this.itemClick(e);
        },
        keydown: function keydown(e) {
          return _this.listKeyDown(e);
        },
        blur: function blur(e) {
          return _this.blurHandler(e);
        },
        focus: function focus(e) {
          return _this.listFocus(e);
        }
      }
    };
    Object.keys(this.events).forEach(function (elementName) {
      Object.keys(_this.events[elementName]).forEach(function (eventName) {
        _this[elementName].addEventListener(eventName, _this.events[elementName][eventName]);
      });
    });
  }
  /**
   * Sets attributes to the results list and inserts it in the DOM.
   */


  _createClass(Listbox, [{
    key: "implementList",
    value: function implementList() {
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

  }, {
    key: "blurHandler",
    value: function blurHandler(e) {
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
  }, {
    key: "itemIndex",
    value: function itemIndex(item) {
      return +item.dataset.itemIndex;
    }
  }, {
    key: "itemsLength",
    value: function itemsLength() {
      return this.suggestions.length;
    }
    /**
     * Handles keydown events on the item list.
     *
     * @param {Event} e
     *   The keydown event.
     */

  }, {
    key: "listKeyDown",
    value: function listKeyDown(e) {
      if (!this.ul.contains(document.activeElement) || e.ctrlKey || e.altKey || e.metaKey) {
        return;
      }

      var changeSelection = false;
      var focused = e.target.closest('[role="option"]');
      var index = this.itemIndex(focused);
      var length = this.itemsLength();

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
          } // Send the focus back to the combobox.


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

  }, {
    key: "listFocus",
    value: function listFocus(e) {// Intentionally empty, can be overridden.
    }
  }, {
    key: "focusItem",
    value: function focusItem(index) {
      var item = this.ul.querySelector("[role=\"option\"][data-item-index=\"".concat(index, "\"]"));

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

  }, {
    key: "highlightItem",
    value: function highlightItem() {
      var item = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

      if (!item) {
        item = this.ul.querySelector('[role="option"]');
      }

      item.setAttribute('aria-selected', true);
      item.focus();
      var selectedIndex = this.itemIndex(item);
      this.notify('highlight', {
        selectedIndex: selectedIndex,
        posinset: selectedIndex + 1,
        setsize: this.itemsLength()
      });
    }
    /**
     * Handles click events on the item list.
     *
     * @param {Event} e
     *   The click event.
     */

  }, {
    key: "itemClick",
    value: function itemClick(e) {
      if (e.button === 0) {
        var focused = e.target.closest('[role="option"]');

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

  }, {
    key: "selectItem",
    value: function selectItem(selectedIndex) {
      this.notify('select', {
        selectedIndex: selectedIndex
      });
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

  }, {
    key: "suggestionItem",
    value: function suggestionItem(suggestion, index) {
      var li = document.createElement('li');
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

  }, {
    key: "displayResults",
    value: function displayResults(results) {
      this.ul.innerHTML = '';
      this.suggestions = results;
      var fragment = document.createDocumentFragment();
      var appendToFragment = fragment.appendChild.bind(fragment);
      results.map(this.suggestionItem).forEach(appendToFragment);
      this.ul.appendChild(fragment);
    }
  }, {
    key: "open",
    value: function open() {
      this.ul.removeAttribute('hidden');
      this.ul.style.zIndex = this.options.listZindex;

      if (this.options.autoFocus) {
        this.highlightItem();
      }
    }
  }, {
    key: "close",
    value: function close() {
      this.ul.setAttribute('hidden', '');
      this.ul.innerHTML = '';
      this.suggestions = [];
      this.notify('focusout');
    }
  }, {
    key: "destroy",
    value: function destroy() {
      this.ul.parentNode.removeChild(this.ul);
    }
  }]);

  return Listbox;
}();

/**
 * This class handles the input events and formatting of the suggestion items to
 * display.
 */

var Combobox = /*#__PURE__*/function () {
  function Combobox(input, options) {
    var _this = this;

    _classCallCheck(this, Combobox);

    this.keyCode = keys;
    this.options = options;
    this.originalInput = input.cloneNode();
    this.input = input;
    this.count = document.querySelectorAll('[data-autocomplete-input]').length;
    this.listboxId = "autocomplete-listbox-".concat(this.count);
    this.input.setAttribute('aria-owns', "".concat(this.listboxId, "-suggestions")); // Create a div that will wrap the input and suggestion list.

    this.wrapper = document.createElement('div');
    this.implementWrapper(); // When applicable, create a live region for announcing suggestion results
    // to assistive technology.

    this.liveRegion = null;
    this.implementLiveRegion();
    this.listbox = new Listbox({
      id: input.getAttribute('aria-owns'),
      notify: this.handleEvent.bind(this)
    }, options);
    this.inputDescribedBy = this.input.getAttribute('aria-describedby');
    this.inputHintRead = false;
    this.implementInput();
    this.implementDescription();
    this.preventCloseOnBlur = false;
    this.isOpened = false; // Events to add.

    this.events = {
      input: {
        blur: function blur(e) {
          return _this.blurHandler(e);
        },
        keydown: function keydown(e) {
          return _this.inputKeyDown(e);
        }
      },
      wrapper: {
        keydown: function keydown(e) {
          return _this.wrapperKeyDown(e);
        }
      }
    };
    Object.keys(this.events).forEach(function (elementName) {
      Object.keys(_this.events[elementName]).forEach(function (eventName) {
        _this[elementName].addEventListener(eventName, _this.events[elementName][eventName]);
      });
    });
    this.appendList();
  }
  /**
   * Sets attributes to the wrapper and inserts it in the DOM.
   */


  _createClass(Combobox, [{
    key: "implementWrapper",
    value: function implementWrapper() {
      this.wrapper.setAttribute('data-autocomplete-wrapper', '');
      this.input.parentNode.appendChild(this.wrapper);
      this.wrapper.appendChild(this.input);
    }
    /**
     * Sets attributes to the input and inserts it in the DOM.
     */

  }, {
    key: "implementInput",
    value: function implementInput() {
      // Add attributes to the input.
      this.input.setAttribute('aria-autocomplete', 'list');
      this.input.setAttribute('autocomplete', 'off');
      this.input.setAttribute('data-autocomplete-input', '');
      this.input.setAttribute('role', 'combobox');
      this.input.setAttribute('aria-expanded', 'false');

      if (!this.input.hasAttribute('id')) {
        this.input.setAttribute('id', "autocomplete-input-".concat(this.count));
      }
    }
    /**
     * Creates a live region for reporting status to assistive technology.
     */

  }, {
    key: "implementLiveRegion",
    value: function implementLiveRegion() {
      // If the liveRegion option is set to true, create a new live region and
      // insert it in the autocomplete wrapper.
      if (this.options.createLiveRegion === true) {
        this.liveRegion = document.createElement('span');
        this.liveRegion.setAttribute('data-autocomplete-live-region', '');
        this.liveRegion.setAttribute('aria-live', 'assertive');
        this.input.parentNode.appendChild(this.liveRegion);
      } // If the liveRegion option is a string, it should be a selector for an
      // already-existing live region.


      if (typeof this.options.liveRegion === 'string') {
        this.liveRegion = document.querySelector(this.options.liveRegion);
      }
    }
    /**
     * Adds assistive hints.
     */

  }, {
    key: "implementDescription",
    value: function implementDescription() {
      var description = document.createElement('span');
      description.textContent = this.minCharsMessage() + this.options.inputAssistiveHint;
      description.classList.add('visually-hidden'); // If the autocomplete input has an pre-existing 'aria-describedby', append
      // autocomplete-specific descriptions to that existing element. Otherwise,
      // create a new element with those descriptions and create a new
      // 'aria-describedby' to associate it with the input.

      if (this.inputDescribedBy) {
        // This is content that is appended to an existing description. It's also
        // content that only needs to be conveyed once. Add an attribute that
        // allows this content to be targeted for removal after it is read once.
        description.setAttribute('data-autocomplete-assistive-hint', "".concat(this.count));
        document.querySelector("[id=\"".concat(this.inputDescribedBy, "\"]")).appendChild(description);
      } else {
        description.setAttribute('id', "assistive-hint-".concat(this.count));
        this.input.setAttribute('aria-describedby', "assistive-hint-".concat(this.count));
        this.wrapper.appendChild(description);
      }
    }
    /**
     * Inserts list into DOM.
     */

  }, {
    key: "appendList",
    value: function appendList() {
      this.wrapper.appendChild(this.listbox.ul);
    }
  }, {
    key: "handleEvent",
    value: function handleEvent(type, detail) {
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
  }, {
    key: "handleSelect",
    value: function handleSelect(_ref) {
      var selectedIndex = _ref.selectedIndex;
      this.close();
      this.input.focus();
      var value = this.suggestions[selectedIndex];
      var event = this.input.dispatchEvent(new CustomEvent('option-selected', {
        bubbles: true,
        cancelable: true,
        detail: {
          value: value
        }
      })); // The event can be cancelled when the input value is manipulated somewhere
      // else.

      if (event) {
        // todo add the template function.
        this.input.value = value.value;
      }
    }
  }, {
    key: "handleHighlight",
    value: function handleHighlight(_ref2) {
      var selectedIndex = _ref2.selectedIndex,
          posinset = _ref2.posinset,
          setsize = _ref2.setsize;
      this.announce(this.options.highlightedAssistiveHint.replace('@selectedItem', this.suggestions[selectedIndex].value).replace('@position', posinset).replace('@count', setsize));
    }
    /**
     * Announces to assistive tech when an item is highlighted.
     *
     * @param {string} message
     *   The list item being selected.
     */

  }, {
    key: "announce",
    value: function announce(message) {
      var _this2 = this;

      window.clearTimeout(this.announceTimeOutId); // Delay the announcement by 500 milliseconds. This prevents unnecessary
      // calls when a user is navigating quickly.

      this.announceTimeOutId = setTimeout(function () {
        return _this2.sendToLiveRegion(message);
      }, 500);
    }
    /**
     * A message stating the number of characters needed to trigger autocomplete.
     *
     * @return {string}
     *  The minimum characters message.
     */

  }, {
    key: "minCharsMessage",
    value: function minCharsMessage() {
      if (this.options.minChars > 1) {
        return "".concat(this.options.minCharAssistiveHint.replace('@count', this.options.minChars), ". ");
      }

      return '';
    }
    /**
     * Sends a message to the configured live region.
     *
     * @param {string} message
     *   The message to be sent to the live region.
     */

  }, {
    key: "sendToLiveRegion",
    value: function sendToLiveRegion(message) {
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

  }, {
    key: "blurHandler",
    value: function blurHandler(e) {
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

  }, {
    key: "inputKeyDown",
    value: function inputKeyDown(e) {
      var keyCode = e.keyCode;

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
  }, {
    key: "wrapperKeyDown",
    value: function wrapperKeyDown(e) {
      var keyCode = e.keyCode;

      if (keyCode === this.keyCode.ESC) {
        this.close();
        this.input.focus();
      }
    }
    /**
     * Displays the results retrieved in inputListener().
     */

  }, {
    key: "displayResults",
    value: function displayResults(results) {
      var _this3 = this;

      this.suggestions = results;
      this.listbox.displayResults(results.map(function (item) {
        return _this3.formatSuggestionItem(item);
      }));
      this.open();
      window.clearTimeout(this.announceTimeOutId); // Delay the results announcement by 1400 milliseconds. This prevents
      // unnecessary calls when a user is typing quickly, and avoids the results
      // announcement being cut short by the screenreader stating the just-typed
      // character.

      this.announceTimeOutId = setTimeout(function () {
        return _this3.sendToLiveRegion(_this3.resultsMessage(results.length));
      }, 1400);
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

  }, {
    key: "formatSuggestionItem",
    value: function formatSuggestionItem(suggestion, li) {
      var propertyToDisplay = this.options.displayLabels ? 'label' : 'value';
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

  }, {
    key: "resultsMessage",
    value: function resultsMessage(count) {
      var message;

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

  }, {
    key: "open",
    value: function open() {
      this.input.setAttribute('aria-expanded', 'true');
      this.isOpened = true;
      this.listbox.ul.style.minWidth = "".concat(this.input.offsetWidth - 4, "px");
      this.listbox.open();

      if (this.options.autoFocus) {
        this.preventCloseOnBlur = true;
      }
    }
    /**
     * Closes the suggestion list.
     */

  }, {
    key: "close",
    value: function close() {
      window.clearTimeout(this.announceTimeOutId);

      if (this.isOpened) {
        this.input.setAttribute('aria-expanded', 'false');
        this.isOpened = false;
        this.listbox.close();
      }
    }
  }, {
    key: "destroy",
    value: function destroy() {
      var _this4 = this;

      Object.keys(this.events).forEach(function (elementName) {
        Object.keys(_this4.events[elementName]).forEach(function (eventName) {
          _this4[elementName].removeEventListener(eventName, _this4.events[elementName][eventName]);
        });
      });
      this.listbox.destroy();
      this.wrapper.parentNode.replaceChild(this.originalInput, this.wrapper);
    }
    /**
     * Removes one-time-only assistive hints.
     */

  }, {
    key: "removeAssistiveHint",
    value: function removeAssistiveHint() {
      if (!this.inputHintRead) {
        if (this.inputDescribedBy) {
          var appendedHint = document.querySelector("[data-autocomplete-assistive-hint=\"".concat(this.count, "\"]"));
          appendedHint.parentNode.removeChild(appendedHint);
        } else {
          this.input.removeAttribute('aria-describedby');
        }

        this.inputHintRead = true;
      }
    }
  }]);

  return Combobox;
}();

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

var _A11yAutocomplete = /*#__PURE__*/function () {
  /**
   * Construct a new A11yAutocomplete class.
   *
   * @param {HTMLElement} input
   *   The element to be used as an autocomplete.
   * @param {A11yAutocomplete~Options} options
   *  Autocomplete options.
   */
  function _A11yAutocomplete(input) {
    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

    _classCallCheck(this, _A11yAutocomplete);

    this.input = input;
    this.supportedOptions = ['source', 'cardinality', 'minChars', 'separatorChar', 'firstCharacterIgnoreList', 'createLiveRegion', 'autoFocus', 'allowRepeatValues', // messages.
    'minCharAssistiveHint', 'inputAssistiveHint', 'noResultsAssistiveHint', 'someResultsAssistiveHint', 'oneResultAssistiveHint', 'highlightedAssistiveHint'];
    var defaultOptions = {
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
      inputAssistiveHint: 'When autocomplete results are available use up and down arrows to review and enter to select. Touch device users, explore by touch or with swipe gestures.',
      noResultsAssistiveHint: 'No results found',
      someResultsAssistiveHint: 'There are @count results available.',
      oneResultAssistiveHint: 'There is one result available.',
      highlightedAssistiveHint: '@selectedItem @position of @count is highlighted'
    };
    this.options = _objectSpread2(_objectSpread2(_objectSpread2({}, defaultOptions), this.filterOptions(options)), this.filterOptions(this.attributesToOptions())); // Preset lists provided as strings should be converted to options.

    if (typeof this.options.source === 'string') {
      this.options.source = JSON.parse(this.options.source);
    }

    if (typeof this.options.source !== 'function' && !Array.isArray(this.options.source)) {
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
      _internal_object: this
    };
    /**
     * Fires after initialization and markup additions.
     *
     * @event A11yAutocomplete#autocomplete-created
     * @property {Class} autocomplete - The autocomplete instance.
     */

    this.triggerEvent('autocomplete-created');
  }

  _createClass(_A11yAutocomplete, [{
    key: "initEventsHandlers",
    value: function initEventsHandlers() {
      var _this = this;

      // Events to add.
      this.events = {
        input: {
          input: function input(e) {
            return _this.inputListener(e);
          },
          blur: function blur(e) {
            return _this.blurHandler(e);
          },
          'option-selected': function optionSelected(e) {
            return _this.optionSelectedListener(e);
          }
        }
      };
      Object.keys(this.events).forEach(function (elementName) {
        Object.keys(_this.events[elementName]).forEach(function (eventName) {
          _this[elementName].addEventListener(eventName, _this.events[elementName][eventName]);
        });
      }); // This is necessary to be able to remove the event listener.

      this.boundFocusinHandler = this.focusinHandler.bind(this); // Monitor listbox opening to trigger related events.

      this.observer = new MutationObserver(function (mutationsList) {
        var _iterator = _createForOfIteratorHelper(mutationsList),
            _step;

        try {
          for (_iterator.s(); !(_step = _iterator.n()).done;) {
            var mutation = _step.value;

            if (mutation.attributeName === 'aria-expanded') {
              var listbox = document.querySelector("#".concat(_this.input.getAttribute('aria-owns')));

              if (mutation.target.getAttribute(mutation.attributeName) === 'false') {
                if (listbox) {
                  listbox.removeEventListener('focusin', _this.boundFocusinHandler);
                }
                /**
                 * Fires after the suggestion list closes.
                 *
                 * @event A11yAutocomplete#autocomplete-close
                 * @property {Class} autocomplete - The autocomplete instance.
                 */


                _this.triggerEvent('autocomplete-close');
              } else {
                listbox.addEventListener('focusin', _this.boundFocusinHandler);
                /**
                 * Fires after the suggestion list opens.
                 *
                 * @event A11yAutocomplete#autocomplete-open
                 * @property {Class} autocomplete - The autocomplete instance.
                 * // should add the result list in the event maybe?
                 */

                _this.triggerEvent('autocomplete-open');
              }
            }
          }
        } catch (err) {
          _iterator.e(err);
        } finally {
          _iterator.f();
        }
      }); // Start observing the target node for configured mutations

      this.observer.observe(this.input, {
        attributes: true
      });
    }
    /**
     * Only accept a subset of supported options.
     *
     * @param options
     */

  }, {
    key: "filterOptions",
    value: function filterOptions(options) {
      var _this2 = this;

      var filteredOptions = {};
      var rejectedOptions = {}; // Later loop on the supported options array instead,
      // need to see rejected options to help debug jquery shim for now.

      Object.keys(options).forEach(function (key) {
        if (_this2.supportedOptions.includes(key)) {
          filteredOptions[key] = options[key];
        } else {
          rejectedOptions[key] = options[key];
        }
      }); // Temporary to help debug the shim.

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

  }, {
    key: "attributesToOptions",
    value: function attributesToOptions() {
      var options = {}; // Any options provided in the `data-autocomplete` attribute will take
      // precedence over those specified in `data-autocomplete-(x)`.

      var dataAutocompleteAttributeOptions = this.input.dataset.autocomplete ? JSON.parse(this.input.dataset.autocomplete) : {};
      var dataset = this.input.dataset; // Loop through all of the input's attributes. Any attributes beginning with
      // `data-autocomplete` will be added to an options object.

      for (var key in dataset) {
        if (key.includes('autocomplete') && key !== 'autocomplete' && key !== 'autocompleteInput') {
          var optionName = key.replace('autocomplete', '');
          optionName = optionName.charAt(0).toLowerCase() + optionName.slice(1);
          var value = dataset[key];

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

      return _objectSpread2(_objectSpread2({}, options), dataAutocompleteAttributeOptions);
    }
    /**
     * Handles blur events.
     *
     * @fires A11yAutocomplete#autocomplete-change
     */

  }, {
    key: "blurHandler",
    value: function blurHandler() {
      // Only trigger the event when the value is changed.
      if (this.input.value === this.orignialValue) {
        return;
      } // Only trigger the event if the listbox is closed.


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
  }, {
    key: "focusinHandler",
    value: function focusinHandler(e) {
      var option = e.target.closest('[role="option"]');

      if (option) {
        this.triggerEvent('autocomplete-highlight', {
          selected: this.suggestions[option.dataset.itemIndex]
        });
      }
    }
  }, {
    key: "optionSelectedListener",
    value: function optionSelectedListener(event) {
      var detail = event.detail;
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

      var selected = this.triggerEvent('autocomplete-select', {
        selected: this.selected
      }, true, event); // If the input can have multiple values, let this class handle setting
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

  }, {
    key: "replaceInputValue",
    value: function replaceInputValue(selected) {
      var value = selected.value;
      var separator = this.separator();

      if (separator.length > 0) {
        var before = this.previousItems(separator);
        this.input.value = "".concat(before).concat(value);
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

  }, {
    key: "separator",
    value: function separator() {
      var cardinality = this.options.cardinality;
      var numItems = this.splitValues().length - 1;
      return numItems < cardinality || cardinality <= 0 ? this.options.separatorChar : '';
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

  }, {
    key: "previousItems",
    value: function previousItems(separator) {
      // Needs some explanations for the regex here.
      var escapedSeparator = separator.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      var regex = new RegExp("^.+".concat(escapedSeparator, "\\s*|"));
      var match = this.inputValue().match(regex)[0];
      return match && match.length > 0 ? "".concat(match.trim(), " ") : '';
    }
    /**
     * Takes input events and has them trigger searches when appropriate.
     *
     * @param {Event} e
     *   The input event.
     */

  }, {
    key: "inputListener",
    value: function inputListener(e) {
      var _this3 = this;

      if (!this.searchTimeOutId || this.options.searchDelay === 0) {
        this.searchTimeOutId = setTimeout(function () {
          _this3.doSearch(e);

          _this3.searchTimeOutId = null;
        }, this.options.searchDelay);
      }
    }
    /**
     * Helper method.
     *
     * @param element
     * @param classes
     */

  }, {
    key: "addClasses",
    value: function addClasses(element, classes) {
      classes && classes.split(' ').forEach(function (className) {
        return element.classList.add(className);
      });
    }
    /**
     * Helper method.
     *
     * @param element
     * @param classes
     */

  }, {
    key: "removeClasses",
    value: function removeClasses(element, classes) {
      classes && classes.split(' ').forEach(function (className) {
        return element.classList.remove(className);
      });
    }
    /**
     * Triggers an autocomplete search.
     *
     * @param {Event} e
     *   The event triggering the search.
     */

  }, {
    key: "doSearch",
    value: function doSearch(e) {
      var _this4 = this;

      if (this.options.disabled) {
        return;
      }

      var searchTerm = this.extractLastInputValue();

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

          this.options.source(searchTerm, function (results) {
            _this4.removeClasses(_this4.input, _this4.options.loadingClass);

            _this4.suggestionItems = results;

            _this4.displayResults();
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
  }, {
    key: "displayResults",
    value: function displayResults() {
      var typed = this.extractLastInputValue();
      this.suggestions = this.suggestionItems;
      var results = this.suggestions;

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
          list: results
        });
        this.combobox.displayResults(results);
      } else {
        this.combobox.close();
      }
    }
    /**
     * Converts all suggestions into an object with value and label properties.
     */

  }, {
    key: "normalizeSuggestionItems",
    value: function normalizeSuggestionItems() {
      this.suggestionItems = this.suggestionItems.map(function (item) {
        if (typeof item === 'string') {
          item = {
            value: item,
            label: item
          };
        } else if (item.value && !item.label) {
          item = {
            value: item.value,
            label: item.value
          };
        } else if (item.label && !item.value) {
          item = {
            value: item.label,
            label: item.label
          };
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

  }, {
    key: "prepareSuggestionList",
    value: function prepareSuggestionList(typed) {
      var _this5 = this;

      this.normalizeSuggestionItems();

      if (typed) {
        this.suggestions = this.suggestionItems.filter(function (item) {
          return _this5.filterResults(item, typed);
        });
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

  }, {
    key: "sortSuggestions",
    value: function sortSuggestions() {
      this.suggestions.sort(function (prior, current) {
        return prior.label.toUpperCase() > current.label.toUpperCase() ? 1 : -1;
      });
    }
    /**
     * Returns the last value of an multi-value textfield.
     *
     * @return {string}
     *   The last value of the input field.
     */

  }, {
    key: "extractLastInputValue",
    value: function extractLastInputValue() {
      return this.splitValues().pop();
    }
    /**
     * Gets the input value.
     *
     * @return {String}
     *   The input value.
     */

  }, {
    key: "inputValue",
    value: function inputValue() {
      return this.input.value;
    }
    /**
     * Helper splitting selections from the autocomplete value.
     *
     * @return {Array}
     *   Array of values, split by comma.
     */

  }, {
    key: "splitValues",
    value: function splitValues() {
      var value = this.inputValue();
      var result = [];
      var quote = false;
      var current = '';
      var valueLength = value.length;

      for (var i = 0; i < valueLength; i++) {
        var character = value.charAt(i);

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

  }, {
    key: "filterResults",
    value: function filterResults(suggestion, typed) {
      var _this$options = this.options,
          firstCharacterIgnoreList = _this$options.firstCharacterIgnoreList,
          cardinality = _this$options.cardinality;
      var suggestionValue = suggestion.value;
      var currentValues = this.splitValues(); // Prevent suggestions if the first input character is in the ignore list,
      // if the suggestion has already been added to the field, or if the maximum
      // number of items have been reached.

      if (firstCharacterIgnoreList.indexOf(typed[0]) !== -1 || cardinality > 0 && currentValues.length > cardinality || currentValues.indexOf(suggestionValue) !== -1 && !this.options.allowRepeatValues) {
        return false;
      }

      return RegExp(this.extractLastInputValue().trim().replace(/[-\\^$*+?.()|[\]{}]/g, '\\$&'), 'i').test(suggestionValue);
    }
    /**
     * Remove all event listeners added by this class.
     * @callback A11yAutocomplete~destroy
     */

  }, {
    key: "destroy",
    value: function destroy() {
      var _this6 = this;

      this.observer.disconnect();
      Object.keys(this.events).forEach(function (elementName) {
        Object.keys(_this6.events[elementName]).forEach(function (eventName) {
          _this6[elementName].removeEventListener(eventName, _this6.events[elementName][eventName]);
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

  }, {
    key: "triggerEvent",
    value: function triggerEvent(type) {
      var additionalData = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var cancelable = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
      var originalEvent = arguments.length > 3 ? arguments[3] : undefined;
      var event = new CustomEvent(type, {
        // we should probably set that to allow use of delegated events.
        bubbles: true,
        detail: _objectSpread2({
          // Only exposed the "supported" set of methods/options.
          autocomplete: this.api
        }, additionalData),
        cancelable: cancelable,
        originalEvent: originalEvent
      });

      if (originalEvent) {
        event.originalEvent = originalEvent;
      }

      return this.input.dispatchEvent(event);
    }
  }]);

  return _A11yAutocomplete;
}();
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


var A11yAutocomplete = function A11yAutocomplete(input) {
  var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  var autocomplete = new _A11yAutocomplete(input, options);
  return autocomplete.api;
};

export { A11yAutocomplete as default };
