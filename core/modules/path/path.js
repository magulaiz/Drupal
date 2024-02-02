/**
 * @file
 * Attaches behaviors for the Path module.
 */
(function ($, Drupal) {
  /**
   * Behaviors for settings summaries on path edit forms.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches summary behavior on path edit forms.
   */
  Drupal.behaviors.pathDetailsSummaries = {
<<<<<<< HEAD
    attach: function attach(context) {
      $(context).find('.path-form').drupalSetSummary(function (context) {
        var pathElement = document.querySelector('.js-form-item-path-0-alias input');
        var path = pathElement && pathElement.value;
        return path ? Drupal.t('Alias: @alias', {
          '@alias': path
        }) : Drupal.t('No alias');
      });
    }
=======
    attach(context) {
      $(context)
        .find('.path-form')
        .drupalSetSummary((context) => {
          const pathElement = document.querySelector(
            '.js-form-item-path-0-alias input',
          );
          const path = pathElement && pathElement.value;
          return path
            ? Drupal.t('Alias: @alias', { '@alias': path })
            : Drupal.t('No alias');
        });
    },
>>>>>>> upstream/11.x
  };
})(jQuery, Drupal);
