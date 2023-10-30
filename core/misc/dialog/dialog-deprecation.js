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
            $(window).trigger('dialog:beforecreate', [
              e.detail.dialog,
              e.currentTarget,
              e.detail.settings,
            ]);
            Drupal.deprecationError({
              message: 'Jquery event dialog:beforecreate will deprecated soon',
            });
          });
        }
      }
    },
  };
})(jQuery, Drupal, once);
