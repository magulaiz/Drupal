/**
 * @file
 * Views dialog behaviors.
 */

(function ($, Drupal, drupalSettings) {
  function handleDialogResize(e) {
    const $modal = $(e.currentTarget);
    const $viewsOverride = $modal.find('[data-drupal-views-offset]');
    const $scroll = $modal.find('[data-drupal-views-scroll]');
    let offset = 0;
    let modalHeight;
    if ($scroll.length) {
      // Add a class to do some styles adjustments.
      $modal.closest('.views-ui-dialog').addClass('views-ui-dialog-scroll');
      // Let scroll element take all the height available.
      [].forEach.call($scroll, (el) => {
        el.style.overflow = 'visible';
        el.style.height = 'auto';
      });
      modalHeight = $modal.height();
      $viewsOverride.each(function () {
        offset += $(this).outerHeight();
      });

      // Take internal padding into account.
      const scrollOffset = $scroll.outerHeight() - $scroll.height();
      $scroll.height(modalHeight - offset - scrollOffset);
      // Reset scrolling properties.
      [].forEach.call($modal, (el) => {
        el.style.overflow = 'hidden';
      });
      [].forEach.call($scroll, (el) => {
        el.style.overflow = 'auto';
      });
    }
  }

  /**
   * Functionality for views modals.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches modal functionality for views.
   * @prop {Drupal~behaviorDetach} detach
   *   Detaches the modal functionality.
   */
  Drupal.behaviors.viewsModalContent = {
    attach(context) {
      $(once('viewsDialog', 'body')).on(
        'dialogContentResize.viewsDialog',
        '.ui-dialog-content',
        handleDialogResize,
      );
      // When expanding details, make sure the modal is resized.
      $(once('detailsUpdate', '.scroll', context)).on(
        'click',
        'summary',
        (e) => {
          $(e.currentTarget).trigger('dialogContentResize');
        },
      );
    },
    detach(context, settings, trigger) {
      if (trigger === 'unload') {
        $(once.remove('viewsDialog', 'body')).off('.viewsDialog');
      }
    },
  };
})(jQuery, Drupal, drupalSettings);
