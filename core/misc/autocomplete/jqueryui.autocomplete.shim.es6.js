/**
 * @file
 * Defines a backwards-compatible shim for jquery.ui.autocomplete.
 */
// cSpell:words qunit
(($, Drupal) => {
  Drupal.autocompleteShim = {
    overrides: {},
    instances: {},
  };

  // Attach jQuery UI shim when autocomplete is initialized by
  // Drupal.Autocomplete.initialize
  document.addEventListener('drupal-autocomplete-init', (e) => {
    const { instance, options } = e.detail;
    Drupal.autocompleteShim.jqueryUiShimInit(instance, options);
  });

  document.addEventListener('autocomplete-destroy', (e) => {
    // Ensure that instances are removed from the Drupal.autocompleteShim when
    // an instance is destroyed.
    delete Drupal.autocompleteShim.instances[e.detail.autocomplete.id];
  });

  /**
   * Apply overrides to specific widget properties and functions.
   *
   * This is for overrides that are expected due to calling jQuery UI widget().
   *
   * @param {A11yAutocomplete} instance
   *   The autocomplete instance.
   * @param {string} propertyToOverride
   *   The A11yAutocomplete class property to override.
   * @param {*} overrideWith
   *   What to override the class property with.
   */
  const applyWidgetOverrides = (instance, propertyToOverride, overrideWith) => {
    // @todo this currently overrides extension points, but it may be necessary
    //   to allow fully overriding everything.
    if (propertyToOverride.substr(0, 1) === '_') {
      instance[propertyToOverride] = overrideWith;
    }
  };

  Drupal.autocompleteShim.defaultOptions = {};

  /**
   * Provides overrides needed for jQuery UIs backwards compatibility.
   *
   * These overrides can only be applied after autocomplete initializes.
   *
   * @param {_A11yAutocomplete} instance
   *   The initialized autocomplete input.
   * @param {object} options
   *   The options sent to autocomplete init.
   */
  Drupal.autocompleteShim.jqueryUiShimInit = (instance, options) => {
    Drupal.autocompleteShim.instances[instance.input.id] = instance;
    let BCMarkupOptions = {};
    const usingBCMarkup = Drupal.hasOwnProperty(
      'jQueryAutocompleteStableMarkup',
    );
    if (usingBCMarkup) {
      BCMarkupOptions = Drupal.jQueryAutocompleteStableMarkup.options;
    }
    const isContentEditable = instance.input.hasAttribute('contenteditable');
    const attributesToOptions = instance.attributesToOptions();

    if (
      attributesToOptions.hasOwnProperty('source') &&
      typeof attributesToOptions.source === 'string'
    ) {
      attributesToOptions.source = JSON.parse(attributesToOptions.source);
    }
    instance.options = instance.initOptions(
      instance.options,
      Drupal.autocompleteShim.defaultOptions,
      BCMarkupOptions,
      options,
      attributesToOptions,
    );

    /**
     * This function is used after the library normalization.
     *
     * @param {object|string} item
     * @return {{label: string, value: string}}
     */
    const jQueryUIAutocompleteNormalizeItem = ({ label, value, item }) => {
      if (typeof item === 'string') {
        return { label, value };
      }
      return { ...item, label, value };
    };

    // Apply class changes.
    instance.applyClasses('input');
    instance.applyClasses('listbox');

    instance.input.setAttribute('data-autocomplete-shim-enabled', '');

    instance.liveRegion = document.querySelector('#drupal-live-announce');

    instance.options.isMultiline =
      instance.input.tagName === 'TEXTAREA' ||
      (instance.input.tagName !== 'INPUT' && isContentEditable);

    // jQuery UI allows repeat values in multivalue inputs.
    // If the option is explicitly set to false, that option was set by the form
    // API and should be preserved. Otherwise set to true.
    if (instance.options.allowRepeatValues === null) {
      instance.options.allowRepeatValues = true;
    }

    /**
     * Alters input keydown behavior to match jQuery UI.
     *
     * @param {Event} e
     *   The keydown event.
     */
    function shimmedInputKeyDown(e) {
      instance.options.suppressKeyPress = false;
      const { keyCode } = e;
      const upDownKeyCodes = [this.keyCode.UP, this.keyCode.DOWN];
      // Update the suppressKeyPress option, which is checked by the keyPress
      // event listener, which calls `preventDefault()` on the event when true.
      if (upDownKeyCodes.includes(keyCode)) {
        instance.options.suppressKeyPress = true;
      }

      if (this.isOpened) {
        // Escape behavior is identical to A11yAutocomplete.
        if (keyCode === this.keyCode.ESC) {
          this.close();
        }

        // In jQuery UI, when the input is focused and a list is open,
        // the list can be accessed via up or down arrows. Only the down arrow
        // accomplishes this in A11yAutocomplete.
        if (upDownKeyCodes.includes(keyCode)) {
          e.preventDefault();
          this.preventCloseOnBlur = true;

          // Highlight the first or last item, depending on whether the up or
          // down arrow was pressed.
          const selector =
            keyCode === this.keyCode.DOWN
              ? 'li[role="option"]'
              : 'li[role="option"]:last-child';
          this.highlightItem(this.listboxWrapper.querySelector(selector));
        }

        // jQuery UI explicitly cancels 'return' keydown events when an item is
        // highlighted, to prevent form submission. This isn't a concern when
        // shimming A11yAutocomplete, as the highlighted item also has focus.
        // This event handling is still present so all jQuery UI autocomplete
        // tests will also pass with the shimmed autocomplete.
        if (keyCode === this.keyCode.RETURN) {
          // If this is not null, then an item is highlighted.
          const active = instance.listboxWrapper.querySelectorAll(
            '.ui-menu-item-wrapper.ui-state-active',
          );
          if (
            active.length ||
            instance.listboxWrapper.contains(document.activeElement)
          ) {
            e.preventDefault();
          }
        }
      }

      // If there is a predefined list, jQuery UI autocomplete allows that list
      // to be opened via the up/down arrow keys. This differs from
      // A11yAutocomplete, only opens lists based on characters being typed.
      if (
        this.input.nodeName === 'INPUT' &&
        !this.isOpened &&
        Array.isArray(this.options.source) &&
        this.options.source.length > 0 &&
        upDownKeyCodes.includes(keyCode)
      ) {
        e.preventDefault();

        // This property is always set to true when arrow keys are used to move
        // into an item list. This prevents the default behavior of the list
        // closing when the input is blurred.
        this.preventCloseOnBlur = true;

        // See if anything has been typed into the input.
        const typed = this.extractLastInputValue();

        // In instances where nothing is typed and there is no character
        // minimum, the list must be opened using something other than
        // displayResults(), as that method requires input to work.
        if (!typed && this.options.minChars < 1) {
          const normalizedResults = this.normalizeSuggestionItems(
            this.options.source,
          );
          this.displayResults(normalizedResults);
        } else {
          this.displayResults(this.suggestions);
        }

        // If the arrow key press resulted in the opening of a list, then
        // highlight the first/last item depending on whether the up/down key
        // was pressed.
        if (this.isOpened) {
          const selector =
            keyCode === this.keyCode.DOWN
              ? 'li[role="option"]'
              : 'li[role="option"]:last-child';
          this.highlightItem(this.listboxWrapper.querySelector(selector));
        }
      }

      // This call is also made in the A11yAutocomplete version of this
      // method. It removes some general assistive hints after the first keydown
      // event to avoid unnecessary repetition.
      this.removeAssistiveHint();
    }
    instance.inputKeyDown = shimmedInputKeyDown;

    /**
     * Creates a suggestion list based on a typed value.
     *
     * The majority of this function is identical to A11yAutocomplete. It is
     * changed at the end to be compatible with jQuery UI extension points.
     *
     * @param {string[]|object.<string, string[]>} suggestionItems
     *   The list of normalized results to display.
     */
    function jQueryDisplayResults(suggestionItems) {
      this.suggestions = suggestionItems;
      this.listboxWrapper.innerHTML = '';
      if (this.suggestions.length) {
        if (this.options.sort !== false) {
          this.suggestions = this.sortSuggestions(this.suggestions);
        }
        this._renderMenu(
          this.listboxWrapper,
          this.suggestions.map(jQueryUIAutocompleteNormalizeItem),
        );
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
        () =>
          this.sendToLiveRegion(this.resultsMessage(this.suggestions.length)),
        1400,
      );
    }
    instance.displayResults = jQueryDisplayResults;

    /**
     * A copy of jQuery UI autocomplete _renderMenu.
     *
     * Copied to the instance so it is available as an extension point.
     *
     * @param {Element} ul
     *   Contains the list items.
     * @param {Object[]} items
     *    Suggestions with 'label' and 'value' properties.
     */
    // eslint-disable-next-line func-names
    instance._renderMenu = function (ul, items) {
      instance.jQuerySuggestionCounter = 0;
      const that = this;
      // eslint-disable-next-line func-names
      $.each(items, function (index, item) {
        instance.jQuerySuggestionCounter = index;
        that._renderItemData(ul, item);
      });
    };

    /**
     * A copy of jQuery UI autocomplete _renderItemData.
     *
     * Copied to the instance so it is available as an extension point.
     *
     * @param {Element} ul
     *   Contains the list items.
     * @param {Object} item
     *    Suggestion with 'label' and 'value' properties.
     *
     * @return {*}
     *   Typically a jQuery Object for an `<li>` element.
     */
    // eslint-disable-next-line func-names
    instance._renderItemData = function (ul, item) {
      return this._renderItem(ul, item).data('ui-autocomplete-item', item);
    };

    const suggestionItem = instance.suggestionItem.bind(instance);
    instance.suggestionItem = function jQuerySuggestionItem({ label, index }) {
      const li = suggestionItem({ label, index });

      // Everything prior to this is logic that also happens in the
      // A11yAutocomplete suggestionItems() method. Below is logic specific
      // to the `<a>` tag added to list items when using this shim on a theme
      // that extends Stable or Stable 9.
      if (instance.hasOwnProperty('addBcListItemClasses')) {
        instance.addBcListItemClasses(li, index);
      }
      return li;
    };

    // Only override _renderItem if backwards compatible markup is not needed.
    // In these instances an override of this function will be supplied by the
    // Stable or Stable 9 theme, which reproduces the _renderItem override
    // provided by Drupal core when jQuery UI Autocomplete was in use.
    if (!usingBCMarkup) {
      /**
       * A copy of jQuery UI autocomplete _renderItem.
       *
       * Copied to the instance so it is available as an extension point.
       *
       * @param {Element} ul
       *   Contains the list items.
       * @param {Object} item
       *    Suggestion with 'label' and 'value' properties.
       *
       * @return {*}
       *   Typically a jQuery Object for an `<li>` element.
       */
      // eslint-disable-next-line func-names
      instance._renderItem = function (ul, item) {
        const li = instance.suggestionItem({
          label: item.label,
          index: instance.jQuerySuggestionCounter,
        });
        return $(li).appendTo(ul);
      };
    }

    // Elements with the contenteditable attribute require different logic than
    // the default behavior which expects a text input.
    if (isContentEditable) {
      // eslint-disable-next-line func-names
      instance.inputValue = function () {
        return this.input.textContent;
      };
      instance.replaceInputValue = function (element) {
        const itemIndex = element
          .closest('[data-autocomplete-item]')
          .getAttribute('data-autocomplete-item');
        /** @type {A11yAutocomplete~Suggestion} */
        const { value, item } = this.suggestions[itemIndex];
        this.selected = item;
        const separator = this.separator();
        if (separator.length > 0) {
          const before = this.previousItems(separator);
          this.input.textContent = `${before}${value}`;
        } else {
          this.input.textContent = value;
        }
      };
    }

    // const originalReplaceInputValue =

    // instance.input.addEventListener('autocomplete-select', (e) => {
    //   if (
    //     e.target.tagName !== 'INPUT' &&
    //     e.target.hasAttribute('contenteditable')
    //   ) {
    //     debugger;
    //   }
    // });

    /**
     * Replicates a jQuery function of the same name.
     *
     * Used for triggering a close when a click occurs anywhere outside of the
     * autocomplete elements. Drupal Autocomplete has this functionality as
     * well but it's achieved via different events.
     *
     * @param {Event} event
     *   A mousedown event.
     */
    const closeOnClickOutside = (event) => {
      const menuElement = instance.listboxWrapper;
      const targetInWidget =
        event.target === instance.input ||
        event.target === menuElement ||
        $.contains(menuElement, event.target);
      if (!targetInWidget) {
        instance.close();
      }
    };
    // jQuery UI will close the autocomplete results on any mousedown that lands
    // outside of the autocomplete widget.
    instance.input.addEventListener('autocomplete-open', () => {
      document.body.addEventListener('mousedown', closeOnClickOutside);
      // Position the list directly under the input.
      $(instance.listboxWrapper).position({
        of: instance.input,
        my: 'left top',
        at: 'left bottom',
        collision: 'none',
      });
    });
    instance.input.addEventListener('autocomplete-close', () => {
      document.body.removeEventListener('mousedown', closeOnClickOutside);
    });

    // jQuery UI has a mousedown listener on the list that prevents default.
    instance.listboxWrapper.addEventListener('mousedown', (e) => {
      e.preventDefault();
    });

    // jQuery UI suppresses keypress events in some instances to address a bug
    // specific to Opera and Firefox, where holding down arrow keys would not
    // result in traversing the list of items. This has since been fixed in both
    // browsers, but the keypress event suppression is used in some jQuery UI
    // Qunit tests, so it remains part of the shim.
    // @see see https://bugs.jqueryui.com/ticket/7269
    instance.input.addEventListener('keypress', function (e) {
      if (instance.options.suppressKeyPress) {
        instance.options.suppressKeyPress = false;
        if (!instance.options.isMultiline || instance.isOpened) {
          e.preventDefault();
        }
      }
    });

    // If the widget itself has been overridden via $.widget, map each
    // overridden property to the autocomplete instance.
    Object.keys(Drupal.autocompleteShim.overrides).forEach(
      (propertyToOverride) => {
        const overrideWith =
          Drupal.autocompleteShim.overrides[propertyToOverride];
        applyWidgetOverrides(instance, propertyToOverride, overrideWith);
      },
    );
  };

  // When available, the original jQuery UI autocomplete is added to $.fn in
  // jqueryui.widget.overrides.js.
  Drupal.autocompleteShim.overrideJqueryUi = function () {
    const oldAutocomplete = $.fn.autocomplete;

    // This provides jQuery UI's autocomplete() function for A11yAutocomplete
    // instances. This reproduces the API surface of jQuery UI autocomplete, but
    // uses A11yAutocomplete for the functionality.
    $.fn.extend({
      autocomplete(...args) {
        // If args[0] is a string, the autocomplete instance is already
        // initialized and the string represents a method the autocomplete should
        // execute.
        if (
          typeof args[0] !== 'string' ||
          !this.length ||
          !this[0].hasAttribute('data-autocomplete-shim-enabled')
        ) {
          // If the shim is loaded but not the original jQuery UI library show
          // an error when code is executed on a non-shimmed element.
          if (!oldAutocomplete) {
            console.error(
              'The jQuery UI Autocomplete library is not loaded on the page. Make sure the dependency to the core/jquery.ui.autocomplete is declared for the element using it.',
            );
            return;
          }
          // Check if autocomplete is initialized.
          if (typeof this.data('ui-autocomplete') === 'undefined') {
            // @todo there are scenarios where jQuery UI autocomplete is being
            //    - used directly
            //    AND
            //    - it is via a widget extending autocomplete
            //  But because this override exists, those uses result in the error:
            //  `cannot call methods on autocomplete prior to initialization;
            //  attempted to call method 'widget'
            //  The following line eliminates THAT error:
            //  oldAutocomplete.apply(this);
            //  However, it introduces a new one, so it's not clear if that is progress.
          }

          return oldAutocomplete.apply(this, args);
        }

        Drupal.deprecationError({
          message:
            'The autocomplete() function is deprecated in drupal:9.4.0 and is removed from drupal:10.0.0. Use the API provided by core/a11y_autocomplete instead. See https://www.drupal.org/node/3083715',
        });
        const id = this.attr('id');

        // Some jQuery UI options can be directly mapped to A11yAutocomplete
        // options..
        const optionMapping = {
          autoFocus: 'autoFocus',
          classes: null,
          delay: 'searchDelay',
          disabled: 'disabled',
          minLength: 'minChars',
          position: null,
          source: null,
        };

        if (!Drupal.autocompleteShim.instances[id]) {
          return;
        }

        const instance = Drupal.autocompleteShim.instances[id];
        const method = args[0];

        switch (method) {
          case 'widget':
            // The widget option returns the autocomplete item list.
            return $(instance.listboxWrapper);
          case 'instance':
            // This is the one method that will not return an exact replica
            // of what jQuery UI would return, as jQuery UI autocomplete
            // returns an object that is very jQuery-integrated. The properties
            // that do not have non-jQuery equivalents are null.
            // eslint-disable-next-line no-case-declarations
            const instanceToReturn = {
              document: $(document),
              element: $(instance.input),
              menu: {
                element: $(instance.listboxWrapper),
              },
              liveRegion: $(instance.liveRegion),
              isMultiLine: instance.options.isMultiLine,
              isNewMenu: null,
              options: instance.options,
              window,
            };

            // Add a warning for instance properties that can't be provided via
            // shim.
            [
              'bindings',
              'eventNamespace',
              'classesElementLookup',
              'focusable',
              'hoverable',
              'uuid',
              'source',
              'valueMethod',
            ].forEach((property) => {
              Object.defineProperty(instanceToReturn, property, {
                get() {
                  // eslint-disable-next-line no-console
                  return console.warn(
                    `The ${property} property is not supported beginning with 9.3, as jQuery UI Autocomplete is no longer part of core. See https://www.drupal.org/node/3083715`,
                  );
                },
              });
            });

            return instanceToReturn;
          case 'disable':
            this.autocomplete('option', 'disabled', true);
            break;
          case 'enable':
            this.autocomplete('option', 'disabled', false);
            break;
          case 'search':
            // The 'search' method performs a search as if the input received
            // input events.

            // A11yAutocomplete expects the input to be focused when a search
            // occurs, even if it's programmatically triggered.
            instance.input.focus();

            // If the args[1] argument is present, it will be the search term.
            if (typeof args[1] === 'string') {
              // The input's value property must always be set as it's used
              // internally by A11yAutocomplete.
              [, instance.input.value] = args;

              // For contenteditable elements, the textContent property must
              // also match the provided value for it to be visible.
              if (instance.input.hasAttribute('contenteditable')) {
                [, instance.input.textContent] = args;
              }
            } else if (instance.input.hasAttribute('contenteditable')) {
              // The input's value property must always be set as it's used
              // internally by A11yAutocomplete.
              instance.input.value = instance.input.textContent;
            }

            // If there is no typed value and no minimum character limit, the
            // 'search' option will display the results of a predefined list,
            // when such a list is available.
            if (
              instance.input.value.length === 0 &&
              instance.options.minChars === 0 &&
              Array.isArray(instance.options.source) &&
              instance.options.source.length > 0
            ) {
              // Do not need to execute applySelectionConstraints because there
              // are no values to filter.
              const normalizedResults = instance.normalizeSuggestionItems(
                instance.options.source,
              );
              instance.displayResults(normalizedResults);
            } else {
              // If args[1] isn't a string, just trigger a search using whatever
              // is currently in the input.
              instance.doSearch($.Event('keydown'));
            }
            break;
          case 'option':
            // If args[2] doesn't exist, and args[1] is an object, treat each
            // args[1] object property as an individual autocomplete option that
            // should be set to the corresponding value.
            if (typeof args[2] === 'undefined' && typeof args[1] === 'object') {
              // Individually set each option specified in the object.
              Object.keys(args[1]).forEach((key) => {
                this.autocomplete('option', key, args[1][key]);
              });
            }

            // If args[2] has a value, then this is setting an option.
            if (typeof args[2] !== 'undefined' && typeof args[1] === 'string') {
              const [, optionName, optionValue] = args;
              const listBoxId = instance.listboxWrapper.getAttribute('id');

              switch (optionName) {
                case 'appendTo':
                  // The option value can be a selector string, element, or jQuery
                  // object. Convert to element.
                  // eslint-disable-next-line no-case-declarations
                  let appendTo = null;
                  if (typeof optionValue === 'string') {
                    appendTo = document.querySelector(optionValue);
                  } else if (optionValue instanceof jQuery) {
                    appendTo = optionValue.length > 0 ? optionValue[0] : null;
                  } else {
                    appendTo = optionValue;
                  }
                  if (!appendTo) {
                    const closestUiFront = $(instance.input).closest(
                      '.ui-front, dialog',
                    );
                    if (closestUiFront.length > 0) {
                      [appendTo] = closestUiFront;
                    }
                  }

                  if (appendTo) {
                    if (!appendTo.contains(instance.listboxWrapper)) {
                      appendTo.appendChild(instance.listboxWrapper);
                    }
                    instance.listboxWrapper = appendTo.querySelector(
                      `#${listBoxId}`,
                    );
                  }

                  // Add attribute that flags the shim initializer to skip the
                  // default behavior of appending the list to `document.body`.
                  instance.input.setAttribute(
                    'data-autocomplete-list-appended',
                    true,
                  );
                  break;
                case 'classes':
                  // This option accepts an object keyed by the default class
                  // of the element receiving the new class, and the value is
                  // the class/classes that should replace that default.
                  Object.keys(optionValue).forEach((key) => {
                    if (
                      key === 'ui-autocomplete' ||
                      key === 'ui-autocomplete-input'
                    ) {
                      const element =
                        key === 'ui-autocomplete'
                          ? instance.listboxWrapper
                          : instance.input;
                      instance.addClasses(element, optionValue[key]);
                      // Remove the default class.
                      instance.removeClasses(element, key);
                    }
                  });
                  break;
                case 'classes.ui-autocomplete':
                  // Add the new class(es).
                  instance.addClasses(instance.listboxWrapper, optionValue);
                  // Remove the default class.
                  instance.removeClasses(
                    instance.listboxWrapper,
                    'ui-autocomplete',
                  );
                  break;
                case 'classes.ui-autocomplete-input':
                  // Add the new class(es).
                  instance.addClasses(instance.input, optionValue);
                  // Remove the default class.
                  instance.removeClasses(
                    instance.input,
                    'ui-autocomplete-input',
                  );
                  break;
                case 'disabled':
                  instance.options.disabled = optionValue;
                  $(instance.listboxWrapper).toggleClass(
                    'ui-autocomplete-disabled',
                    optionValue,
                  );
                  break;
                case 'position':
                  $(instance.listboxWrapper).position({
                    of: instance.input,
                    my: 'left top',
                    at: 'left bottom',
                    collision: 'none',
                    ...optionValue,
                  });
                  break;
                case 'source':
                  // In jQuery UI autocomplete, 'source' can be one of three
                  // types:
                  // - Function: A callback function that can be used to connect
                  //   any data source to Autocomplete
                  // - String: An URL to an endpoint that returns JSON.
                  // - Array: An array of list items. Can be an array of strings
                  //   or of objects with `label` and/or `value` properties.
                  if (typeof optionValue === 'function') {
                    instance.options.source = (search, results) => {
                      optionValue({ term: search }, results);
                    };
                  } else if (typeof optionValue === 'string') {
                    instance.options.source =
                      Drupal.Autocomplete.ajaxSearchProvider(optionValue);
                  } else {
                    instance.options.source = optionValue;
                  }
                  break;
                default:
                  // If the option is an event name, then the optionValue is
                  // a handler for that event.
                  if (
                    [
                      'change',
                      'close',
                      'create',
                      'focus',
                      'open',
                      'response',
                      'search',
                      'select',
                    ].includes(optionName)
                  ) {
                    this.on(`autocomplete${optionName}`, optionValue);
                  }

                  // Some jQuery UI autocomplete options have 1:1 equivalents.
                  // Those cases are identified and mapped here.
                  if (optionMapping.hasOwnProperty(optionName)) {
                    instance.options[optionMapping[optionName]] = optionValue;
                    // Duplicate the option with jQuery UI naming for BC.
                    instance.options[optionName] = optionValue;
                  }
                  break;
              }
            } else if (typeof args[1] === 'string') {
              // If args[1] is a string, it is the name of an option. Return the
              // value of that option.
              if (optionMapping[args[1]]) {
                return instance.options[optionMapping[args[1]]];
              }
              return instance.options[args[1]];
            }
            break;
          default:
            // Some jQuery UI methods have identically A11yAutocomplete methods
            // that provide the same functionality and can simply be called.
            if (typeof instance[method] === 'function') {
              instance[method]();
            }
            break;
        }

        return this;
      },
    });
  };

  Drupal.autocompleteShim.overrideJqueryUi();

  // If $.ui.autocomplete exists, it needs to remain there for modules that want
  // to use jQuery UI autocomplete via contrib module or the deprecated
  // core/jquery.ui.autocomplete library.
  if (!$.ui.hasOwnProperty('autocomplete')) {
    $.ui.autocomplete = () => {
      console.warn(
        '$.ui.autocomplete no longer exists due to its removal in Drupal 9.4.0. Existing uses of $().autocomplete() will continue to work. See https://www.drupal.org/node/3083715',
      );
    };
  }
})(jQuery, Drupal);
