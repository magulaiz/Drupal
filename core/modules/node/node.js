/**
 * @file
 * Defines JavaScript behaviors for the node module.
 */

(function ($, Drupal, drupalSettings) {
  /**
   * Behaviors for tabs in the node edit form.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches summary behavior for tabs in the node edit form.
   */
  Drupal.behaviors.nodeDetailsSummaries = {
<<<<<<< HEAD
    attach: function attach(context) {
      var $context = $(context);
      $context.find('.node-form-author').drupalSetSummary(function (context) {
        var nameElement = context.querySelector('.field--name-uid input');
        var name = nameElement && nameElement.value;
        var dateElement = context.querySelector('.field--name-created input');
        var date = dateElement && dateElement.value;
=======
    attach(context) {
      const $context = $(context);

      $context.find('.node-form-author').drupalSetSummary((context) => {
        const nameElement = context.querySelector('.field--name-uid input');
        const name = nameElement && nameElement.value;
        const dateElement = context.querySelector('.field--name-created input');
        const date = dateElement && dateElement.value;
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

<<<<<<< HEAD
        if ($optionsContext.find('input').is(':checked')) {
          $optionsContext.find('input:checked').next('label').each(function () {
            vals.push(Drupal.checkPlain(this.textContent.trim()));
          });
          return vals.join(', ');
=======
      $context.find('.node-form-options').drupalSetSummary((context) => {
        const $optionsContext = $(context);
        const values = [];

        if ($optionsContext.find('input:checked').length) {
          $optionsContext
            .find('input:checked')
            .next('label')
            .each(function () {
              values.push(Drupal.checkPlain(this.textContent.trim()));
            });
          return values.join(', ');
>>>>>>> upstream/11.x
        }

        return Drupal.t('Not promoted');
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
