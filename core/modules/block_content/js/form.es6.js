/**
 * @file
 * Defines Javascript behaviors for the block content form.
 */

(function ($, Drupal) {
  /**
   * Behaviors for summaries for tabs in the block content edit form.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches summary behavior for tabs in the block content edit form.
   */
  Drupal.behaviors.blockContentFormSummaries = {
    attach(context) {
      const $context = $(context);

      $context.find('.block-content-form-author').drupalSetSummary(context => {
        const $authorContext = $(context);
        const name = $authorContext.find('.field--name-uid input').val();

        if (name) {
          return Drupal.t('By @name', {'@name': name});
        }
      });
    },
  };
})(jQuery, Drupal);
