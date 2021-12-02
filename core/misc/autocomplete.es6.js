/**
 * @file
 * Autocomplete based on jQuery UI.
 */

(function ($, Drupal) {
  let autocomplete;

  /**
   * Helper splitting terms from the autocomplete value.
   *
   * @function Drupal.autocomplete.splitValues
   *
   * @param {string} value
   *   The value being entered by the user.
   *
   * @return {Array}
   *   Array of values, split by comma.
   */
  function autocompleteSplitValues(value) {
    // We will match the value against comma-separated terms.
    const result = [];
    let quote = false;
    let current = '';
    const valueLength = value.length;
    let character;

    for (let i = 0; i < valueLength; i++) {
      character = value.charAt(i);
      if (character === '"') {
        current += character;
        quote = !quote;
      } else if (character === ',' && !quote) {
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
   * Returns the last value of a multi-value textfield.
   *
   * @function Drupal.autocomplete.extractLastTerm
   *
   * @param {string} terms
   *   The value of the field.
   *
   * @return {string}
   *   The last value of the input field.
   */
  function extractLastTerm(terms) {
    return autocomplete.splitValues(terms).pop();
  }

  /**
   * The search handler is called before a search is performed.
   *
   * @function Drupal.autocomplete.options.search
   *
   * @param {object} event
   *   The event triggered.
   *
   * @return {bool}
   *   Whether to perform a search or not.
   */
  function searchHandler(event) {
    const options = autocomplete.options;

    if (options.isComposing) {
      return false;
    }

    const term = autocomplete.extractLastTerm(event.target.value);
    // Abort search if the first character is in firstCharacterBlacklist.
    if (
      term.length > 0 &&
      options.firstCharacterBlacklist.indexOf(term[0]) !== -1
    ) {
      return false;
    }
    // Only search when the term is at least the minimum length.
    return term.length >= options.minLength;
  }

  /**
   * JQuery UI autocomplete source callback.
   *
   * @param {object} request
   *   The request object.
   * @param {function} response
   *   The function to call with the response.
   */
  function sourceData(request, response) {
    const elementId = this.element.attr('id');

    if (!(elementId in autocomplete.cache)) {
      autocomplete.cache[elementId] = {};
    }

    /**
     * Filter through the suggestions removing all terms already tagged and
     * display the available terms to the user.
     *
     * @param {object} suggestions
     *   Suggestions returned by the server.
     */
    function showSuggestions(suggestions) {
      const tagged = autocomplete.splitValues(request.term);
      const il = tagged.length;
      for (let i = 0; i < il; i++) {
        const index = suggestions.indexOf(tagged[i]);
        if (index >= 0) {
          suggestions.splice(index, 1);
        }
      }
      response(suggestions);
    }

    // Get the desired term and construct the autocomplete URL for it.
    const term = autocomplete.extractLastTerm(request.term);

    /**
     * Transforms the data object into an array and update autocomplete results.
     *
     * @param {object} data
     *   The data sent back from the server.
     */
    function sourceCallbackHandler(data) {
      autocomplete.cache[elementId][term] = data;

      // Send the new string array of terms to the jQuery UI list.
      showSuggestions(data);
    }

    // Check if the term is already cached.
    if (autocomplete.cache[elementId].hasOwnProperty(term)) {
      showSuggestions(autocomplete.cache[elementId][term]);
    } else {
      const options = $.extend(
        { success: sourceCallbackHandler, data: { q: term } },
        autocomplete.ajax,
      );
      $.ajax(this.element.attr('data-autocomplete-path'), options);
    }
  }

  /**
   * Handles an autocompletefocus event.
   *
   * @return {bool}
   *   Always returns false.
   */
  function focusHandler() {
    return false;
  }

  /**
   * Handles an autocompleteselect event.
   *
   * @param {jQuery.Event} event
   *   The event triggered.
   * @param {object} ui
   *   The jQuery UI settings object.
   *
   * @return {bool}
   *   Returns false to indicate the event status.
   */
  function selectHandler(event, ui) {
    const terms = autocomplete.splitValues(event.target.value);
    // Remove the current input.
    terms.pop();
    // Add the selected item.
    terms.push(ui.item.value);

    event.target.value = terms.join(', ');
    // Return false to tell jQuery UI that we've filled in the value already.
    return false;
  }

  /**
   * Override jQuery UI _renderItem function to output HTML by default.
   *
   * @param {jQuery} ul
   *   jQuery collection of the ul element.
   * @param {object} item
   *   The list item to append.
   *
   * @return {jQuery}
   *   jQuery collection of the ul element.
   */
  function renderItem(ul, item) {
    return $('<li>').append($('<a>').html(item.label)).appendTo(ul);
  }

  /**
   * Attaches the autocomplete behavior to all required fields.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the autocomplete behaviors.
   * @prop {Drupal~behaviorDetach} detach
   *   Detaches the autocomplete behaviors.
   */
  Drupal.behaviors.autocomplete = {
    attach(context) {
      const optionsToOriginalMethods = {
        source: sourceData,
        focus: focusHandler,
        search: searchHandler,
        select: selectHandler,
        renderItem,
        cache: {},
        splitValues: autocompleteSplitValues,
        extractLastTerm,
        minLength: 1,
        firstCharacterBlacklist: '',
        isComposing: false,
        ajax: {
          dataType: 'json',
          jsonp: false,
        },
      };
      once('legacy-autocomplete', 'body').forEach(() => {
        document.addEventListener('autocomplete-created', (e) => {
          const autocompleteInput =
            e.detail.autocomplete._internal_object.input;

          // Loop through the jQuery autocomplete options that are settable via
          // Drupal.autocomplete and route those overrides to the shim.
          Object.keys(Drupal.autocomplete.options).forEach((option) => {
            if (optionsToOriginalMethods.hasOwnProperty(option)) {
              Drupal.deprecationError({
                message:
                  'Setting autocomplete widget options via Drupal.autocomplete.options is deprecated in drupal:9.4.0 and is removed from drupal:10.0.0. Override Drupal.Autocomplete.defaultOptions using the its new API instead. See https://www.drupal.org/node/3083715',
              });
              if (
                optionsToOriginalMethods[option] !==
                Drupal.autocomplete.options[option]
              ) {
                const optionName =
                  option === 'renderItem' ? '_renderItem' : option;

                // The isComposing property does not need to be shimmed.
                // - It is not a jQuery autocomplete property, and in the
                // - The issue it exists to address is not a concern with the
                //   A11y_Autocomplete implementation. IME use does not trigger
                //   search until composition is complete.
                if (optionName !== 'isComposing') {
                  $(autocompleteInput).autocomplete(
                    optionName,
                    Drupal.autocomplete.options[option],
                  );
                }
              }
            }
          });

          Object.keys(Drupal.autocomplete).forEach((key) => {
            if (key === 'options') {
              return;
            }

            // For Aj
            const originalValue = ['ajax', 'cache'].includes(key)
              ? JSON.stringify(optionsToOriginalMethods[key])
              : optionsToOriginalMethods[key];
            const newValue = ['ajax', 'cache'].includes(key)
              ? JSON.stringify(Drupal.autocomplete[key])
              : Drupal.autocomplete[key];

            if (originalValue !== newValue) {
              const instance = e.detail.autocomplete._internal_object;
              switch (key) {
                case 'ajax':
                  // @todo warning specific to this property.
                  break;
                case 'cache':
                  // @todo warning specific to this property.
                  break;
                case 'splitValues':
                  Drupal.deprecationError({
                    message:
                      'Drupal.autocomplete.splitValues is deprecated in drupal:9.4.0 and is removed from drupal:10.0.0. Override the splitValues method of A11y_Autocomplete instead. See https://www.drupal.org/node/3083715',
                  });
                  // eslint-disable-next-line func-names,max-nested-callbacks
                  instance.splitValues = function () {
                    return Drupal.autocomplete[key](this.inputValue());
                  };
                  break;
                case 'extractLastTerm':
                  Drupal.deprecationError({
                    message:
                      'Drupal.autocomplete.extractLastTerm is deprecated in drupal:9.4.0 and is removed from drupal:10.0.0. Override the inputValue method of A11y_Autocomplete instead. See https://www.drupal.org/node/3083715',
                  });
                  // eslint-disable-next-line func-names
                  instance.extractLastInputValue = function () {
                    return Drupal.autocomplete[key](this.inputValue());
                  };
                  break;
                default:
                  break;
              }
            }
          });
        });
      });
    },
  };

  /**
   * Autocomplete object implementation.
   *
   * @namespace Drupal.autocomplete
   */
  autocomplete = {
    cache: {},
    // Exposes options to allow overriding by contrib.
    splitValues: autocompleteSplitValues,
    extractLastTerm,
    // jQuery UI autocomplete options.

    /**
     * JQuery UI option object.
     *
     * @name Drupal.autocomplete.options
     */
    options: {
      source: sourceData,
      focus: focusHandler,
      search: searchHandler,
      select: selectHandler,
      renderItem,
      minLength: 1,
      // Custom options, used by Drupal.autocomplete.
      firstCharacterBlacklist: '',
      // Custom options, indicate IME usage status.
      isComposing: false,
    },
    ajax: {
      dataType: 'json',
      jsonp: false,
    },
  };

  Drupal.autocomplete = autocomplete;
})(jQuery, Drupal);
