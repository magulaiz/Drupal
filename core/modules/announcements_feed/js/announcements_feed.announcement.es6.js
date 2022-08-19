(function ($, Drupal, once) {
  /**
   * Update the announce icon when tray is opened.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.announce = {
    attach(context) {
      const announcement = once(
        'announce-new',
        '.announce-new',
        context,
      ).shift();
      if (announcement) {
        announcement.addEventListener('click', function (e) {
          e.currentTarget.classList.remove('announce-new');
        });
      }
    },
  };

  $(window).on(
    'dialog:aftercreate',
    function (event, dialog, $element, settings) {
      if (settings.announce) {
        $('[data-drupal-announce-trigger]')
          .off('click')
          .on('click.announce', function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (dialog.open) {
              $('.announce-dialog .announce-close').trigger('click');
            } else {
              dialog.show();
            }
          });

        $('.announce-dialog .announce-close').on('click', function () {
          $('[data-drupal-announce-trigger]').trigger('click');
          dialog.open = false;
        });
      }
    },
  );
})(jQuery, Drupal, once);
