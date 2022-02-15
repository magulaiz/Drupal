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
   *     without a corresponding ckeditor5-stylesheets setting. Note that if a
   *     primary (non-base theme) theme has ckeditor5-stylesheets configured,
   *     any associated base themes will not trigger a warning, as the primary
   *     theme could potentially be providing the needed ckeditor5-stylesheets
   *     for itself and its base themes.
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
        const editorSettings = document.querySelector(
          '[data-drupal-selector="editor-settings-wrapper"]',
        );

        /**
         * Adds a ckeditor_stylesheets warning to the message container.
         */
        const addCkeditorStylesheetsWarning = () => {
          selectMessages.add(
            drupalSettings.ckeditor5.ckeditor_stylesheets_warning,
            {
              type: 'warning',
            },
          );
        };

        /**
         * Adds a warning if the selected editor is ckeditor5, otherwise clears
         * the message container.
         */
        const updateWarningStatus = () => {
          if (
            select.value === 'ckeditor5' &&
            !select.classList.contains('error')
          ) {
            addCkeditorStylesheetsWarning();
          } else {
            editorSettings.hidden = false;
            selectMessages.clear();
          }
        };

        updateWarningStatus();

        // Declare the observer first so the observer callback can access it.
        let editorSelectObserver = null;

        // Listen to text format selection changes.
        select.addEventListener('change', () => {
          // Create the observer if it does not yet exist.
          if (editorSelectObserver) {
            // An observer is used because during the select change event, it is
            // not yet known if validation prevented the switch to CKEditor 5.
            // The observer listens for the removal of the 'disabled' attribute on
            // the editor <select>, as this means the AJAX callback has completed
            // and the form is in a state suitable for determining if the
            // stylesheet warning is needed.
            editorSelectObserver = new MutationObserver((mutations) => {
              for (let i = 0; i < mutations.length; i++) {
                // When the select input is no longer disabled, the AJAX request
                // is complete and the UI is in a state where it can be determined
                // if the ckeditor_stylesheets warning is needed.
                if (
                  mutations[i].type === 'attributes' &&
                  mutations[i].attributeName === 'disabled' &&
                  !select.disabled
                ) {
                  updateWarningStatus();

                  // Once a ckeditor_stylesheets warning is generated, the
                  // observer can stop monitoring. Monitoring will resume if
                  // the select element changes.
                  editorSelectObserver.disconnect();
                }
              }
            });
          }

          // Enable the observer as soon as the select element changes, so it
          // can monitor when the AJAX request has completed by checking when
          // the element is no longer disabled.
          editorSelectObserver.observe(select, {
            attributes: true,
          });
        });
      }
    },
  };
})(Drupal, once, drupalSettings);
