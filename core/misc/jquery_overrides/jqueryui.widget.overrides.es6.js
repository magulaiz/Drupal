/**
 * @file
 * Overrides the jQuery UI widget function.
 */
(($) => {
  // The jQuery UI `widget()` function will be overridden below. Make copies of
  // the original functions so that they can be called later.
  const oldWidget = $.widget;
  const oldWidgetExtend = $.widget.extend;
  const oldWidgetBridge = $.widget.bridge;

  // eslint-disable-next-line func-names
  $.widget = function (...args) {
    if ($.ui.autocomplete) {
      if (
        args[0] === 'ui.autocomplete' &&
        args[1] === $.ui.autocomplete &&
        typeof args[2] === 'object'
      ) {
        const supportedProperties = [
          'options',
          '_renderItem',
          '_renderMenu',
          '_resizeMenu',
        ];
        const unsupported = Object.keys(args[2]).filter((key) => {
          return !supportedProperties.includes(key);
        });

        // Support for widget is limited only to options and
        // autocomplete-specific methods. Any uses beyond that will trigger an
        // error
        if (unsupported.length === 0) {
          Drupal.autocompleteShim.overrides = args[2];
        } else {
          throw new Error(
            `Unsupported use of $.widget to extend autocomplete. The following constructor properties are not supported by the Drupal Autocomplete backwards compatibility layer: ${unsupported.join(
              ', ',
            )}`,
          );
        }
      }
    }

    // Run jQuery UI's default widget() to ensure that widget factory has impact
    // to newly initialized jQuery UI instances.
    const oldWidgetBound = oldWidget.bind(this);
    const oldWidgetResult = oldWidgetBound(...args);

    // The shim needs to override the widget factory overridden
    // $.fn.autocomplete.
    Drupal.autocompleteShim.overrideJqueryUi();

    return oldWidgetResult;
  };

  // Bring back widget prototype functions that were removed due to $.widget
  // being overwritten.
  $.widget.extend = oldWidgetExtend;
  $.widget.bridge = oldWidgetBridge;
})(jQuery);
