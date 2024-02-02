/**
 * @file
 * Defines JavaScript behaviors for the media form.
 */

(function ($, Drupal) {
  /**
   * Behaviors for summaries for tabs in the media edit form.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches summary behavior for tabs in the media edit form.
   */
  Drupal.behaviors.mediaFormSummaries = {
<<<<<<< HEAD
    attach: function attach(context) {
      $(context).find('.media-form-author').drupalSetSummary(function (context) {
        var nameInput = context.querySelector('.field--name-uid input');
        var name = nameInput && nameInput.value;
        var dateInput = context.querySelector('.field--name-created input');
        var date = dateInput && dateInput.value;
=======
    attach(context) {
      $(context)
        .find('.media-form-author')
        .drupalSetSummary((context) => {
          const nameInput = context.querySelector('.field--name-uid input');
          const name = nameInput && nameInput.value;
          const dateInput = context.querySelector('.field--name-created input');
          const date = dateInput && dateInput.value;
>>>>>>> upstream/11.x

          if (name && date) {
            return Drupal.t('By @name on @date', {
              '@name': name,
              '@date': date,
            });
          }
          if (name) {
            return Drupal.t('By @name', { '@name': name });
          }
          if (date) {
            return Drupal.t('Authored on @date', { '@date': date });
          }
        });
    },
  };
})(jQuery, Drupal);
