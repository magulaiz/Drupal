/**
 * @file
 * JavaScript for the node content editing form.
 */

(function ($, Drupal) {
  /**
   * Behaviors for setting summaries on content type form.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches summary behaviors on content type edit forms.
   */
  Drupal.behaviors.contentTypes = {
<<<<<<< HEAD
    attach: function attach(context) {
      var $context = $(context);
      $context.find('#edit-submission').drupalSetSummary(function (context) {
        var vals = [];
        vals.push(Drupal.checkPlain($(context).find('#edit-title-label')[0].value) || Drupal.t('Requires a title'));
        return vals.join(', ');
      });
      $context.find('#edit-workflow').drupalSetSummary(function (context) {
        var vals = [];
        $(context).find('input[name^="options"]:checked').next('label').each(function () {
          vals.push(Drupal.checkPlain(this.textContent));
        });

        if (!$(context).find('#edit-options-status').is(':checked')) {
          vals.unshift(Drupal.t('Not published'));
=======
    attach(context) {
      const $context = $(context);
      // Provide the vertical tab summaries.
      $context.find('#edit-submission').drupalSetSummary((context) => {
        const values = [];
        values.push(
          Drupal.checkPlain($(context).find('#edit-title-label')[0].value) ||
            Drupal.t('Requires a title'),
        );
        return values.join(', ');
      });
      $context.find('#edit-workflow').drupalSetSummary((context) => {
        const values = [];
        $(context)
          .find('input[name^="options"]:checked')
          .next('label')
          .each(function () {
            values.push(Drupal.checkPlain(this.textContent));
          });
        if ($(context).find('#edit-options-status:checked').length === 0) {
          values.unshift(Drupal.t('Not published'));
>>>>>>> upstream/11.x
        }
        return values.join(', ');
      });
<<<<<<< HEAD
      $('#edit-language', context).drupalSetSummary(function (context) {
        var vals = [];
        vals.push($('.js-form-item-language-configuration-langcode select option:selected', context)[0].textContent);
        $('input:checked', context).next('label').each(function () {
          vals.push(Drupal.checkPlain(this.textContent));
        });
        return vals.join(', ');
      });
      $context.find('#edit-display').drupalSetSummary(function (context) {
        var vals = [];
        var $editContext = $(context);
        $editContext.find('input:checked').next('label').each(function () {
          vals.push(Drupal.checkPlain(this.textContent));
        });
=======
      $('#edit-language', context).drupalSetSummary((context) => {
        const values = [];
>>>>>>> upstream/11.x

        values.push(
          $(
            '.js-form-item-language-configuration-langcode select option:selected',
            context,
          )[0].textContent,
        );

        $('input:checked', context)
          .next('label')
          .each(function () {
            values.push(Drupal.checkPlain(this.textContent));
          });

        return values.join(', ');
      });
      $context.find('#edit-display').drupalSetSummary((context) => {
        const values = [];
        const $editContext = $(context);
        $editContext
          .find('input:checked')
          .next('label')
          .each(function () {
            values.push(Drupal.checkPlain(this.textContent));
          });
        if ($editContext.find('#edit-display-submitted:checked').length === 0) {
          values.unshift(Drupal.t("Don't display post information"));
        }
        return values.join(', ');
      });
    },
  };
})(jQuery, Drupal);
