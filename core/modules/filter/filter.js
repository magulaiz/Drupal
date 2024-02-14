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
        const target = event.target;
        const { value } = event.target;
        const closestEl = target.closest('.js-filter-wrapper');
        const $this = $(closestEl);
        $this
          .find('[data-drupal-format-id]')
          .hide()
          .filter(`[data-drupal-format-id="${value}"]`)
          .show();
      }

      // Define the closest function for finding the closest ancestor with a given selector
      function closest(element, selector) {
        while (element && !element.matches(selector)) {
          element = element.parentNode;
        }
        return element;
      }

      const contextEl = document.querySelector('.js-filter-guidelines');
      const closestWrapper = closest(contextEl, '.js-filter-wrapper');
      if (closestWrapper) {
        const $context = $(contextEl);
        const selectElement = $(closestWrapper).find('select.js-filter-list');
        $context.find(':header').hide();
        selectElement
          .on('change.filterGuidelines', updateFilterGuidelines)
          // Need to trigger the namespaced event to avoid triggering formUpdated
          // when initializing the select.
          .trigger('change.filterGuidelines');
      }
    },
  };
})(jQuery, Drupal);
