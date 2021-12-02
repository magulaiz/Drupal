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
  behaviors.autocompleteShimTestJqueryUi = {
    attach() {
      once(
        'autocomplete_shim_test',
        '#autocomplete, #autocomplete-contenteditable, #autocomplete-textarea',
      ).forEach((element) => $(element).autocomplete());
    },
  };
})(jQuery, Drupal, once);
