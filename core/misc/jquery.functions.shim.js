/**
 * @file
 * Defines a backwards-compatible shim for jQuery .show, .hide, .toggle.
 */

(($, Drupal) => {
  const originalHide = $.fn.hide;
  $.fn.hide = function (...args) {
    if (args.length) {
      Drupal.deprecationError({
        message: `jQuery hide() function no longer accepts arguments`,
      });
      return originalHide.apply(this, args);
    }

    this.each((index, item) => {
      if (item instanceof Element) {
        item.toggleAttribute('hidden', true);
      }
    });
    return this;
  };

  const originalShow = $.fn.show;
  $.fn.show = function (...args) {
    if (args.length) {
      Drupal.deprecationError({
        message: `jQuery show() function no longer accepts arguments`,
      });
      return originalShow.apply(this, args);
    }

    this.each((index, item) => {
      if (item instanceof Element) {
        item.toggleAttribute('hidden', false);
      }
    });
    return this;
  };

  const originalToggle = $.fn.toggle;
  $.fn.toggle = function (...args) {
    if (
      args.length === 0 ||
      (args.length === 1 && typeof args[0] === 'boolean')
    ) {
      this.each((index, item) => {
        if (item instanceof Element) {
          item.toggleAttribute('hidden', args[0]);
        }
      });
      return this;
    }

    Drupal.deprecationError({
      message: `jQuery toggle() function only accepts a single boolean argument`,
    });
    return originalToggle.apply(this, args);
  };

  const originalFadeIn = $.fn.fadeIn;
  $.fn.fadeIn = function (...args) {
    this.css('display', 'none');
    this.removeAttr('hidden');

    return originalFadeIn.apply(this, args);
  };
})(jQuery, Drupal);
