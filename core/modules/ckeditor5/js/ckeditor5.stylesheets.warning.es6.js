/**
 * @file
 * Provides warnings regarding the ckeditor_stylesheets setting.
 */

((Drupal, once, drupalSettings) => {
  /**
   * Provide a warning regarding the ckeditor_stylesheets theme setting.
   *
   * The ckeditor_stylesheets theme setting does not work with CKEditor 5.
   * A different setting, ckeditor5-stylesheets, is used instead. This
   * behavior provides a warning in the configuration form for a text format
   * when.
   * - The text format is using CKEditor 5.
   * - There are themes or base themes using the ckeditor_stylesheets setting
   *   without a corresponding ckeditor5-stylesheets setting. Note that if a
   *   primary (non-base theme) theme has ckeditor5-stylesheets configured,
   *   any associated base themes will not trigger a warning, as the primary
   *   theme could potentially be providing the needed ckeditor5-stylesheets
   *   for itself and its base themes.
   *
   * @type {Drupal~behavior}
   *
   * @see https://www.drupal.org/node/3259165
   */
  Drupal.behaviors.ckEditor5StylesheetsWarn = {
    attach: function attach() {
      const editorSelect = once(
        'editor-select-stylesheet-warning',
        '[data-drupal-selector="filter-format-edit-form"] [data-drupal-selector="edit-editor-editor"], [data-drupal-selector="filter-format-add-form"] [data-drupal-selector="edit-editor-editor"]',
      );

      if (
        typeof editorSelect[0] !== 'undefined' &&
        drupalSettings.ckeditor5 &&
        drupalSettings.ckeditor5.ckeditor_stylesheets_warning
      ) {
        const select = editorSelect[0];

        // Add a container for messages above the text format select element.
        const selectMessageContainer = document.createElement('div');
        select.parentNode.insertBefore(selectMessageContainer, select);
        const selectMessages = new Drupal.Message(selectMessageContainer);

        /**
         * Adds a ckeditor_stylesheets warning to the message container.
         */
        const addCkeditorStylesheetsWarning = () => {
          selectMessages.add(
            drupalSettings.ckeditor5.ckeditor_stylesheets_warning,
            {
              type: 'warning',
              id: 'ckeditor_stylesheets_warning',
            },
          );
        };

        /**
         * Adds a warning if the selected editor is CKEditor 5, otherwise clears
         * any existing ckeditor_stylesheets warnings.
         */
        const updateWarningStatus = () => {
          // If the selected editor is CKEditor 5 and there are no validation
          // errors, provide the ckeditor_stylesheets warning.
          if (
            select.value === 'ckeditor5' &&
            !select.hasAttribute('data-error-switching-to-ckeditor5')
          ) {
            addCkeditorStylesheetsWarning();
          } else if (selectMessages.select('ckeditor_stylesheets_warning')) {
            selectMessages.remove('ckeditor_stylesheets_warning');
          }
        };

        updateWarningStatus();

        // This observer listens for two different attribute changes that, when
        // they occur, may require adding or removing the ckeditor_stylesheets
        // warning.
        // - If the disabled attribute was removed, which is potentially due to
        //   an AJAX update having completed.
        // - If the data-error-switching-to-ckeditor5 attribute was removed,
        //   which means a switch to CKEditor 5 that was previously blocked due
        //   to validation errors has resumed and completed.
        const editorSelectObserver = new MutationObserver((mutations) => {
          for (let i = 0; i < mutations.length; i++) {
            // TRUE when the element has switched from disabled to not disabled.
            const switchToCKEditor5Complete =
              mutations[i].type === 'attributes' &&
              mutations[i].attributeName === 'disabled' &&
              !select.disabled;
            // TRUE when a switch to CKEditor 5 blocked by validation is no
            // longer blocked.
            const fixedErrorsPreventingSwitchToCKEditor5 =
              mutations[i].type === 'attributes' &&
              mutations[i].attributeName ===
                'data-error-switching-to-ckeditor5' &&
              !select.hasAttribute('data-error-switching-to-ckeditor5');
            if (
              switchToCKEditor5Complete ||
              fixedErrorsPreventingSwitchToCKEditor5
            ) {
              updateWarningStatus();
            }
          }
        });

        editorSelectObserver.observe(select, {
          attributes: true,
        });
      }
    },
  };
})(Drupal, once, drupalSettings);
