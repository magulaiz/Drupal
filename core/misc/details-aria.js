/**
 * @file
 * Add aria attribute handling for details and summary elements.
 */

(function ($, Drupal) {
  /**
   * Handles `aria-expanded` and `aria-pressed` attributes on details elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.detailsAria = {
    attach() {
      $(once('detailsAria', 'body')).on(
        'click.detailsAria',
        'summary',
        (event) => {
          const $summary = $(event.currentTarget);
          const open =
            $(event.currentTarget.parentNode)[0].getAttribute('open') === 'open'
              ? 'false'
              : 'true';

          $summary[0].setAttribute('aria-expanded', open);
          $summary[0].setAttribute('aria-pressed', open);
        },
      );
    },
  };
})(jQuery, Drupal);
