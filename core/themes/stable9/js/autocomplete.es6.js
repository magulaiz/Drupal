/**
 * @file
 * Defines a backwards-compatible shim for jquery.ui.autocomplete.
 */

(($, Drupal) => {
  Drupal.jQueryAutocompleteStableMarkup = {};
  document.addEventListener('drupal-autocomplete-init', (e) => {
    const { instance, options } = e.detail;

    Drupal.jQueryAutocompleteStableMarkup.options = {
      // Add jQuery UI classes so the autocomplete is styled the same as its
      // jQuery UI predecessor.
      inputClass: 'ui-autocomplete-input',
      ulClass: 'ui-menu ui-widget ui-widget-content ui-autocomplete ui-front',
      loadingClass: 'ui-autocomplete-loading',
      // In jQuery UI autocomplete, the ui-menu-item-wrapper class is added to
      // the `<a>` tag inside each list item. A11yAutocomplete does not wrap
      // items in`<a>` tags, so this class is moved to the `<li>` which provides
      // a visually identical autocomplete experience to the previous jQuery UI
      // autocomplete.
      itemClass: 'ui-menu-item',
    };
    Drupal.jQueryAutocompleteStableMarkup.init(instance, options);
  });

  Drupal.jQueryAutocompleteStableMarkup.init = (instance, options) => {
    // Bypass option filtering.
    instance.options = Object.assign(
      instance.options,
      Drupal.autocompleteShim.stableOptions,
    );

    // Apply class changes.
    instance.implementInput();
    instance.implementList();

    if (!instance.input.hasAttribute('data-autocomplete-list-appended')) {
      const listBoxId = instance.ul.getAttribute('id');
      const uiFront = $(instance.input).closest('.ui-front, dialog');

      // If the autocomplete is contained by an element with the class
      // 'ui-front' or a dialog, append the class to that element. Otherwise
      // append it to the document body.
      const appendTo =
        uiFront.length > 0 ? uiFront[0] : document.querySelector('body');
      appendTo.appendChild(instance.ul);
      instance.ul = document.querySelector(`#${listBoxId}`);
    }

    /**
     * A copy of jQuery UI autocomplete _renderItem.
     *
     * Copied to the instance so it is available as an extension point.
     * Includes markup overrides to match the overrides in Drupal's
     * original implementation of jQuery UI autocomplete.
     *
     * @param {Element} ul
     *   Contains the list items.
     * @param {Object} item
     *    Suggestion with 'label' and 'value' properties.
     *
     * @return {*}
     *   Typically a jQuery Object for an `<li>` element.
     */
    // eslint-disable-next-line func-names
    instance._renderItem = function (ul, item) {
      const propertyToDisplay = instance.options.displayLabels
        ? 'label'
        : 'value';
      // Drupal core's implementation of jQuery UI autocomplete adds an `<a>`.
      return $('<li>')
        .append($('<a>').html(item[propertyToDisplay]))
        .appendTo(ul);
    };

    instance.addBcListItemClasses = function (li, index) {
      const a = li.querySelector('a');
      a.classList.add('ui-menu-item-wrapper');
      a.setAttribute('id', `ui-id-${index}`);
    };

    // If the input receives focus, remove the 'ui-state-active' class from all
    // result items.
    instance.input.addEventListener('focus', () => {
      instance.ul
        .querySelectorAll('.ui-menu-item-wrapper.ui-state-active')
        .forEach((element) => {
          element.classList.remove('ui-state-active');
        });
    });

    // When a result item is highlighted, jQuery UI adds a 'ui-state-active'
    // class to it.
    instance.input.addEventListener('autocomplete-highlight', () => {
      instance.ul
        .querySelectorAll('.ui-menu-item-wrapper.ui-state-active')
        .forEach((element) => {
          element.classList.remove('ui-state-active');
        });
      document.activeElement
        .querySelector('.ui-menu-item-wrapper')
        .classList.add('ui-state-active');
    });

    // When a list item is hovered over, the ui-state-active class is added to
    // the anchor within that item. Only one item at a time can have this class.
    // A currently focused item will have this class, but hovering another item
    // in the list will move the ui-state-active class to the hovered item,
    // whether or not it is focused.
    instance.ul.addEventListener('mouseover', (e) => {
      instance.ul.querySelectorAll('a').forEach((item) => {
        item.classList.remove('ui-state-active');
      });

      if (e.target.tagName === 'LI') {
        e.target.querySelector('a').classList.add('ui-state-active');
      } else if (e.target.tagName === 'A') {
        e.target.classList.add('ui-state-active');
      }
    });

    // jQuery UI autocomplete does not have a wrapper, so remove the wrapper
    // added by A11yAutocomplete.
    $(instance.input).unwrap('[data-autocomplete-wrapper]');
    $(instance.input).data('ui-autocomplete', instance);
  };
})(jQuery, Drupal);
