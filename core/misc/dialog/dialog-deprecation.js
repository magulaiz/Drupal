(function ($, Drupal, once) {
  /**
   * Trigger jQuery events and deprecation messages.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   */
  Drupal.behaviors.dialogDeprecation = {
    attach(context, settings) {
      if (context === document) {
        const doc = once(
          'drupal-dialog-deprecation-listener',
          document.documentElement,
        )?.shift();
        if (doc) {
          // Here we listen new customEvent dialogBeforecreate.
          // Trigger old event and deprecation message.
          doc.addEventListener('dialogBeforecreate', (e) => {
            // We trigger jQuery Event ONLY if something listens it.
            // EG: $.event.global?.['dialog:beforecreate'].

            if ($.event.global?.['dialog:beforecreate']) {
              $(window).trigger('dialog:beforecreate', [
                e.detail.dialog,
                $(e.target),
                e.detail.settings,
              ]);
              Drupal.deprecationError({
                message:
                  'Jquery event dialog:beforecreate will deprecated soon',
              });
            }
          });

          doc.addEventListener('dialogAftercreate', (e) => {
            // We trigger jQuery Event ONLY if something listens it.
            // EG: $.event.global?.['dialog:aftercreate'].

            if ($.event.global?.['dialog:aftercreate']) {
              $(window).trigger('dialog:aftercreate', [
                e.detail.dialog,
                $(e.target),
                e.detail.settings,
              ]);
              Drupal.deprecationError({
                message: 'Jquery event dialog:aftercreate will deprecated soon',
              });
            }
          });

          doc.addEventListener('dialogBeforeclose', (e) => {
            // We trigger jQuery Event ONLY if something listens it.
            // EG: $.event.global?.['dialog:beforeclose'].

            if ($.event.global?.['dialog:beforeclose']) {
              $(window).trigger('dialog:beforeclose', [
                e.detail.dialog,
                $(e.target),
              ]);
              Drupal.deprecationError({
                message: 'Jquery event dialog:beforeclose will deprecated soon',
              });
            }
          });

          doc.addEventListener('dialogAfterclose', (e) => {
            // We trigger jQuery Event ONLY if something listens it.
            // EG: $.event.global?.['dialog:afterclose'].

            if ($.event.global?.['dialog:afterclose']) {
              $(window).trigger('dialog:afterclose', [
                e.detail.dialog,
                $(e.target),
              ]);
              Drupal.deprecationError({
                message: 'Jquery event dialog:afterclose will deprecated soon',
              });
            }
          });
        }
      }
    },
  };
})(jQuery, Drupal, once);
