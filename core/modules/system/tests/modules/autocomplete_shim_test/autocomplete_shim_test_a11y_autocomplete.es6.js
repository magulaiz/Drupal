/**
 * @file
 *  Attaches Accessible Autocomplete to inputs for testing.
 */
(($, { behaviors }, once) => {
  /**
   * Attaches the autocomplete behavior to elements used for testing.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the autocomplete to inputs used for testing.
   */
  behaviors.autocompleteShimTestA11yAutocomplete = {
    attach() {
      once(
        'autocomplete_shim_test',
        '#autocomplete-contenteditable, #autocomplete-textarea',
      ).forEach(Drupal.Autocomplete.initialize);
    },
  };
})(jQuery, Drupal, once);
