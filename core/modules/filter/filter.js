/**
 * @file
 * Attaches behavior for the Filter module.
 */

(function ($, Drupal) {
  /**
   * Displays the guidelines of the selected text format automatically.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches behavior for updating filter guidelines.
   */
  Drupal.behaviors.filterGuidelines = {
    attach(context) {
      function updateFilterGuidelines(event) {
        const { value } = event.target;
        const element = event.target;
        const filterWrapper = element.closest('.js-filter-wrapper');
        const formatElements = filterWrapper.querySelectorAll(
          '[data-drupal-format-id]',
        );

        Array.from(formatElements).forEach((element) => {
          element.style.display = 'none';
        });

        const filteredElement = filterWrapper.querySelector(
          `[data-drupal-format-id="${value}"]`,
        );
        if (filteredElement) {
          filteredElement.style.display = 'block';
        }
      }

      $(once('filter-guidelines', '.js-filter-guidelines', context))
        .find(':header')
        .hide()
        .closest('.js-filter-wrapper')
        .find('select.js-filter-list')
        .on('change.filterGuidelines', updateFilterGuidelines)
        // Need to trigger the namespaced event to avoid triggering formUpdated
        // when initializing the select.
        .trigger('change.filterGuidelines');
    },
  };
})(jQuery, Drupal);
