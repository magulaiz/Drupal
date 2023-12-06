(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.ckeditorImageFileUploadValidation = {
    attach(context, settings) {
      // Using native javascript listener for .ck-editor, to intercept creditor's listening.
      const ckEditor = document.querySelector('.ck-editor');
      if (ckEditor) {
        ckEditor.addEventListener(
          'change',
          // eslint-disable-next-line func-names
          function (event) {
            if (
              event.target &&
              event.target.localName === 'input' &&
              event.target.className === 'ck-hidden'
            ) {
              const { files } = event.target;
              if (files.length > 0) {
                const fileSize = files[0].size;

                const textareaElement = ckEditor.previousElementSibling;
                // Check the selected text formatter.
                if (
                  textareaElement &&
                  textareaElement.hasAttribute('data-editor-active-text-format')
                ) {
                  const textFormat = textareaElement.getAttribute(
                    'data-editor-active-text-format',
                  );
                  const maxSize =
                    settings.editor.formats[textFormat].editorSettings.config
                      .drupalImageUpload.imageUploadSettings.max_size;
                  if (textFormat && maxSize) {
                    if (fileSize > maxSize) {
                      const maxSizeMB = maxSize / 1024 / 1024;
                      // eslint-disable-next-line no-alert
                      alert(
                        `File size exceeds the allowed limit ${maxSizeMB} MB. Please select a smaller file`,
                      );
                      $(event.target).val('');
                    }
                  }
                }
              }
            }
          },
          true,
        );
      }
    },
  };
})(jQuery, Drupal, drupalSettings);
