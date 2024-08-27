/**
 * @file media_library.click_to_select.js
 */

(($, Drupal) => {
  /**
   * Allows users to select an element which checks a hidden checkbox.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches behavior for selecting media library item.
   */
  Drupal.behaviors.ClickToSelect = {
    attach(context) {
      $(
        once(
          'media-library-click-to-select',
          '.js-click-to-select-trigger',
          context,
        ),
      ).on('click', (event) => {
        // Links inside the trigger should not be click-able.
        event.preventDefault();
        const closestElement = event.currentTarget.closest(
          '.js-click-to-select',
        );
        // Click the hidden checkbox when the trigger is clicked.
        const $input = $(closestElement).find(
          '.js-click-to-select-checkbox input',
        );
        $input.prop('checked', !$input.prop('checked')).trigger('change');
      });

      $(
        once(
          'media-library-click-to-select',
          '.js-click-to-select-checkbox input',
          context,
        ),
      )
        .on('change', ({ currentTarget }) => {
          const closestElement = currentTarget.closest('.js-click-to-select');
          $(closestElement).toggleClass(
            'checked',
            $(currentTarget).prop('checked'),
          );
        })
        // Adds is-focus class to the click-to-select element.
        .on('focus blur', ({ currentTarget, type }) => {
          const closestElement = currentTarget.closest('.js-click-to-select');
          $(closestElement).toggleClass('is-focus', type === 'focus');
        });

      // Adds hover class to the click-to-select element.
      $(
        once(
          'media-library-click-to-select-hover',
          '.js-click-to-select-trigger, .js-click-to-select-checkbox',
          context,
        ),
      ).on('mouseover mouseout', ({ currentTarget, type }) => {
        const closestElement = currentTarget.closest('.js-click-to-select');
        $(closestElement).toggleClass('is-hover', type === 'mouseover');
      });
    },
  };
})(jQuery, Drupal);
