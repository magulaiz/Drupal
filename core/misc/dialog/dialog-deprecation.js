(function ($, Drupal, once) {
  const doc = once(
    'drupal-dialog-deprecation-listener',
    document.documentElement,
  )?.shift();
  if (doc) {
    const eventSpecial = {
      handle($event) {
        const $element = $($event.target);
        const event = $event.originalEvent;
        const dialog = event.detail.dialog;
        const settings = [event.detail.dialog, $element];
        if (event.detail?.settings) {
          settings.push(event.detail.settings);
        }
        $event.handleObj.handler.apply(this, [
          $event,
          dialog,
          $element,
          settings,
        ]);
      },
    };

    $.event.special['dialog:beforecreate'] = eventSpecial;
    $.event.special['dialog:aftercreate'] = eventSpecial;
    $.event.special['dialog:beforeclose'] = eventSpecial;
    $.event.special['dialog:afterclose'] = eventSpecial;

    const listenDialogEvent = (event) => {
      const windowEvents = $._data(window, 'events');
      const isWindowHasDialogListener = windowEvents[event.type];
      if (isWindowHasDialogListener) {
        Drupal.deprecationError({
          message: `Jquery event ${event.type} will deprecated soon`,
        });
      }
    };

    [
      'dialog:beforecreate',
      'dialog:aftercreate',
      'dialog:beforeclose',
      'dialog:afterclose',
    ].forEach((e) => doc.addEventListener(e, listenDialogEvent));
  }
})(jQuery, Drupal, once);
