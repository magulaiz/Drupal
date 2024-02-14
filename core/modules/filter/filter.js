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

      const filter = ('filter-guidelines', '.js-filter-guidelines', context);
      const headings = filter.querySelectorAll('h1, h2, h3, h4, h5, h6');
      headings.forEach((el) => {
        const closestEl = el.closest('.js-filter-wrapper');
        el.style.display = 'none';
        $(closestEl)
          .find('select.js-filter-list')
          .on('change.filterGuidelines', updateFilterGuidelines)
          // Need to trigger the namespaced event to avoid triggering formUpdated
          // when initializing the select.
          .trigger('change.filterGuidelines');
      });
    },
  };
})(jQuery, Drupal);
