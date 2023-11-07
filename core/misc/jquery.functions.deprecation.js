/**
 * @file
 * Deprecation messages for jQuery .show, .hide, .toggle.
 */

(($, Drupal) => {
  const originalHide = $.fn.hide;
  $.fn.hide = function (...args) {
    Drupal.deprecationError({
      message: `jQuery hide() function is deprecated`,
    });
    return originalHide.apply(this, args);
  };

  const originalShow = $.fn.show;
  $.fn.show = function (...args) {
    Drupal.deprecationError({
      message: `jQuery show() function is deprecated`,
    });
    return originalShow.apply(this, args);
  };

  const originalToggle = $.fn.toggle;
  $.fn.toggle = function (...args) {
    Drupal.deprecationError({
      message: `jQuery toggle() function is deprecated`,
    });
    return originalToggle.apply(this, args);
  };
})(jQuery, Drupal);
