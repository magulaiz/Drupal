(function (Drupal, once) {
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

      window.addEventListener('dialog:aftercreate', function (event, dialog, $element, settings) {
        if (settings.announce) {
          const handler = (e) => {
            document.querySelector('[data-drupal-announce-trigger]')
              .removeEventListener('click', handler);
            e.preventDefault();
            e.stopPropagation();

            if (dialog.open) {
              document.querySelector('.announce-dialog .announce-close').click();
              return;
            }

            dialog.show();
          }

          document.querySelector('[data-drupal-announce-trigger]')
            .addEventListener('click.announce', handler);

          document.querySelector('.announce-dialog .announce-close').addEventListener('click', function () {
            dialog.open = false;
          });

          const afterClose = (event, dialog) => {
            window.removeEventListener('dialog:afterclose', afterClose);
            document.querySelector('[data-drupal-announce-trigger]').click();
          }
          window.addEventListener('dialog:afterclose', afterClose);
        }
      });
    },
  };
})(Drupal, once);
