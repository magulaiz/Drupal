/**
 * @file
 * CKEditor 5 Style admin behavior.
 */

(function ($, Drupal) {
  /**
   * Provides the summary for the "style" plugin settings vertical tab.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches summary behavior to the plugin settings vertical tab.
   */
  Drupal.behaviors.ckeditor5StyleSettingsSummary = {
    attach() {
      $('[data-ckeditor5-plugin-id="ckeditor5_style"]').drupalSetSummary(
        (context) => {
          const stylesElement = document.querySelector(
            '[data-drupal-selector="edit-editor-settings-plugins-ckeditor5-style-styles"]',
          );
          const styles = stylesElement ? stylesElement.value.trim() : '';

          if (styles.length === 0) {
            return Drupal.t('No styles configured');
          }

          const count = styles.split('\n').length;
          return Drupal.t('@count styles configured', { '@count': count });
        },
      );
    },
  };
})(jQuery, Drupal);
