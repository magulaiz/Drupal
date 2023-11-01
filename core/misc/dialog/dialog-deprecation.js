(function ($, Drupal, once) {
  /**
   * Trigger jQuery events and deprecation messages.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   */
  Drupal.behaviors.dialogDeprecation = {
    attach(context) {
      if (context === document) {
        const doc = once(
          'drupal-dialog-deprecation-listener',
          document.documentElement,
        )?.shift();
        if (doc) {

          const eventsMapping = {
            dialogBeforecreate: 'dialog:beforecreate',
            dialogAftercreate: 'dialog:aftercreate',
            dialogBeforeclose: 'dialog:beforeclose',
            dialogAfterclose: 'dialog:afterclose',
          };

          const listenDialogEvent = (event) => {
            const windowEvents = $._data(window, 'events');
            const newType = event.type;
            const oldType = eventsMapping[newType];
            const isWindowHasDialogListener = windowEvents[oldType];

            if (isWindowHasDialogListener) {
              const jqueryDialogEventParameters = [
                event.detail.dialog,
                $(event.target),
              ];
              if (event.detail?.settings) {
                jqueryDialogEventParameters.push(event.detail.settings);
              }

              $(window).trigger(oldType, jqueryDialogEventParameters);

              Drupal.deprecationError({
                message: `Jquery event ${oldType} will deprecated soon`,
              });
            }
          };

          Object.keys(eventsMapping).forEach((e) =>
            doc.addEventListener(e, listenDialogEvent),
          );
        }
      }
    },
  };
})(jQuery, Drupal, once);
