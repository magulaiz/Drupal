/**
 * @file
 * Views dialog behaviors.
 */

(function ($, Drupal, drupalSettings, bodyScrollLock) {
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
      $scroll.toArray().forEach((element) => {
        Object.assign(element.style, {
          overflow: 'visible',
          height: 'auto',
        });
      });
      modalHeight = $modal.height();
      $viewsOverride.toArray().forEach((element) => {
        offset += $(element).outerHeight();
      });

      // Take internal padding into account.
      const scrollOffset = $scroll.outerHeight() - $scroll.height();
      $scroll.height(modalHeight - offset - scrollOffset);
      // Reset scrolling properties.
      $modal.toArray().forEach((element) => {
        element.style.overflow = 'hidden';
      });
      $scroll.toArray().forEach((element) => {
        element.style.overflow = 'auto';
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
          e.currentTarget?.dispatchEvent(
            new CustomEvent('dialogContentResize', { bubbles: true }),
          );
        },
      );
    },
    detach(context, settings, trigger) {
      if (trigger === 'unload') {
        $(once.remove('viewsDialog', 'body')).off('.viewsDialog');
      }
    },
  };

  /**
   * Binds a listener on dialog creation to handle Views modal scroll.
   *
   * @param {jQuery.Event} e
   *   The event triggered.
   * @param {Drupal.dialog~dialogDefinition} dialog
   *   The dialog instance.
   * @param {jQuery} $element
   *   The jQuery collection of the dialog element.
   */
  window.addEventListener('dialog:aftercreate', (e) => {
    const $element = $(e.target);
    const $scroll = $element.find('.scroll');
    if ($scroll.length) {
      bodyScrollLock.unlock($element.get(0));
      bodyScrollLock.lock($scroll.get(0));
    }
  });
})(jQuery, Drupal, drupalSettings, bodyScrollLock);
