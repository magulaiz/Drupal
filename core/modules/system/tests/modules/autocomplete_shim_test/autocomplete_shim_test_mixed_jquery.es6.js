/**
 * @file
 *  Attaches jQuery UI autocomplete to inputs for testing.
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
  behaviors.autocompleteShimTestMixedJquery = {
    attach() {
      // Initialize #autocomplete as jQuery
      once('autocomplete_shim_test_j', '#autocomplete').forEach((element) =>
        $(element).autocomplete(),
      );
      // Initialize all other inputs as A11y_autocomplete.
      once(
        'autocomplete_shim_test_a',
        '#direct-jquery, #autocomplete-contenteditable, #autocomplete-textarea',
      ).forEach(Drupal.Autocomplete.initialize);
    },
  };
})(jQuery, Drupal, once);
