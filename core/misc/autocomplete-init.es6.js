((Drupal, drupalSettings, A11yAutocomplete, once) => {
  Drupal.Autocomplete = {};
  Drupal.Autocomplete.instances = {};

  // These are the default options when initializing an autocomplete. These
  // can be overridden in several ways. These are listed in order of highest
  // precedence to least:
  // 1 - An object literal in the input's `data-autocomplete` attribute with the
  //   structure `{camelCaseOptionName: value}`.
  // 2 - An input's `data-autocomplete-(hyphen delimited option name)`
  //   attribute.
  // 3 - The options object provided to the constructor.
  Drupal.Autocomplete.defaultOptions = {
    // Do not create an autocomplete-specific live region since
    // #drupal-live-announce will be used.
    createLiveRegion: false,
    // The assistive hint overrides intentionally use placeholders without
    // having them populated in the Drupal.t() call. These placeholders are
    // replaced with their expected values in A11yAutocomplete, which uses
    // the same placeholder format.
    minCharAssistiveHint: Drupal.t(
      'Type @count or more characters for results',
    ),
    noResultsAssistiveHint: Drupal.t('No results found'),
    moreThanMaxResultsAssistiveHint: Drupal.t(
      'There are at least @count results available. Type additional characters to refine your search.',
    ),
    someResultsAssistiveHint: Drupal.t('There are @count results available.'),
    oneResultAssistiveHint: Drupal.t('There is one result available.'),
    inputAssistiveHint: Drupal.t(
      'When autocomplete results are available use up and down arrows to review and enter to select.  Touch device users, explore by touch or with swipe gestures.',
    ),
    highlightedAssistiveHint: Drupal.t(
      '@selectedItem @position of @count is highlighted',
    ),
  };

  /**
   * Initializes an input to use Drupal Autocomplete.
   *
   * @param {Element} autocompleteInput
   *  The autocomplete input.
   */
  Drupal.Autocomplete.initialize = (autocompleteInput) => {
    const options = Drupal.Autocomplete.defaultOptions || {};
    // The default cardinality of A11yAutocomplete is 1. Fields in Drupal
    // without explicitly set cardinality should be set to -1, which
    // provides unlimited cardinality. This setting is applied via the
    // data-autocomplete-cardinality attribute as it the highest
    // precedence way to set an option.
    if (!autocompleteInput.hasAttribute('data-autocomplete-cardinality')) {
      autocompleteInput.setAttribute('data-autocomplete-cardinality', '-1');
    }

    if (
      autocompleteInput.hasAttribute(
        'data-autocomplete-first-character-blacklist',
      )
    ) {
      options.firstCharacterIgnoreList = autocompleteInput.getAttribute(
        'data-autocomplete-first-character-blacklist',
      );
      Drupal.deprecationError({
        message:
          'The data-autocomplete-first-character-blacklist attribute is deprecated in drupal:9.2.0 and is removed from drupal:10.0.0. Use data-autocomplete-first-character-ignore-list instead See https://www.drupal.org/node/3083715',
      });
    }

    const autocomplete = A11yAutocomplete(autocompleteInput, options);
    const instance = autocomplete._internal_object;

    document.dispatchEvent(
      new CustomEvent('drupal-autocomplete-init', {
        detail: {
          instance,
          options,
        },
      }),
    );

    /**
     * Sends a message to assistive technology.
     *
     * This overrides A11yAutocomplete.sendToLiveRegion() so screen reader
     * announcements are handled by Drupal.announce instead of live regions
     * provided by A11yAutocomplete.
     *
     * @param {string} message
     *   The message to be announced.
     */
    function autocompleteSendToLiveRegion(message) {
      Drupal.announce(message, 'assertive');
    }
    instance.sendToLiveRegion = autocompleteSendToLiveRegion;
    return autocomplete;
  };

  /**
   * Attaches the autocomplete behavior to fields configured for autocomplete.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the autocomplete behaviors.
   */
  Drupal.behaviors.autocomplete = {
    attach() {
      if (once('autocomplete-lifecycle', 'body').length) {
        document.addEventListener('autocomplete-destroy', (e) => {
          delete Drupal.Autocomplete.instances[e.detail.autocomplete.id];
        });
        document.addEventListener('autocomplete-created', (e) => {
          Drupal.Autocomplete.instances[e.detail.autocomplete.id] =
            e.detail.autocomplete;
        });
      }
      once('autocomplete-init', 'input.form-autocomplete').forEach(
        Drupal.Autocomplete.initialize,
      );
    },
    detach(context, settings, trigger) {
      if (trigger === 'unload') {
        once
          .remove('autocomplete-init', 'input.form-autocomplete', context)
          .forEach((input) => {
            if (input.id in Drupal.Autocomplete.instances) {
              Drupal.Autocomplete.instances[input.id].destroy();
            }
          });
      }
    },
  };
})(Drupal, drupalSettings, A11yAutocomplete, once);
