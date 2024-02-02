/**
 * @file
 * Attaches comment behaviors to the entity form.
 */

(function ($, Drupal) {
  /**
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.commentFieldsetSummaries = {
<<<<<<< HEAD
    attach: function attach(context) {
      var $context = $(context);
      $context.find('fieldset.comment-entity-settings-form').drupalSetSummary(function (context) {
        return Drupal.checkPlain($(context).find('.js-form-item-comment input:checked').next('label')[0].textContent);
      });
    }
=======
    attach(context) {
      const $context = $(context);
      $context
        .find('fieldset.comment-entity-settings-form')
        .drupalSetSummary((context) =>
          Drupal.checkPlain(
            $(context)
              .find('.js-form-item-comment input:checked')
              .next('label')[0].textContent,
          ),
        );
    },
>>>>>>> upstream/11.x
  };
})(jQuery, Drupal);
