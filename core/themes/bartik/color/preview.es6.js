/**
 * @file
 * Preview for the Bartik theme.
 */
(function ($, Drupal, drupalSettings) {
  Drupal.color = {
    logoChanged: false,
    callback(context, settings, $form) {
      // Change the logo to be the real one.
      if (!this.logoChanged) {
        $('.color-preview .color-preview-logo img').attr(
          'src',
          drupalSettings.color.logo,
        );
        this.logoChanged = true;
      }
      // Remove the logo if the setting is toggled off.
      if (drupalSettings.color.logo === null) {
        $('div').remove('.color-preview-logo');
      }

      const $colorPreview = $form.find('.color-preview');
      const $colorPalette = $form.find('.js-color-palette');

      // Solid background.
      [].forEach.call($colorPreview, (el) => {
        el.style.backgroundColor = $colorPalette
          .find('input[name="palette[bg]"]')
          .val();
      });

      // Text preview.
      [].forEach.call(
        $colorPreview.find(
          '.color-preview-main h2, .color-preview .preview-content',
        ),
        (el) => {
          el.style.color = $colorPalette
            .find('input[name="palette[text]"]')
            .val();
        },
      );

      [].forEach.call($colorPreview.find('.color-preview-content a'), (el) => {
        el.style.color = $colorPalette
          .find('input[name="palette[link]"]')
          .val();
      });

      // Sidebar block.
      const $colorPreviewBlock = $colorPreview.find(
        '.color-preview-sidebar .color-preview-block',
      );
      [].forEach.call($colorPreviewBlock, (el) => {
        el.style.backgroundColor = $colorPalette
          .find('input[name="palette[sidebar]"]')
          .val();
        el.style.borderColor = $colorPalette
          .find('input[name="palette[sidebarborders]"]')
          .val();
      });

      // Footer wrapper background.
      [].forEach.call(
        $colorPreview.find('.color-preview-footer-wrapper'),
        (el) => {
          el.style.backgroundColor = $colorPalette
            .find('input[name="palette[footer]"]')
            .val();
        },
      );

      // CSS3 Gradients.
      const gradientStart = $colorPalette
        .find('input[name="palette[top]"]')
        .val();
      const gradientEnd = $colorPalette
        .find('input[name="palette[bottom]"]')
        .val();

      $colorPreview
        .find('.color-preview-header')
        .attr(
          'style',
          `background-color: ${gradientStart}; background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, from(${gradientStart}), to(${gradientEnd})); background-image: -moz-linear-gradient(-90deg, ${gradientStart}, ${gradientEnd});`,
        );

      [].forEach.call($colorPreview.find('.color-preview-site-name'), (el) => {
        el.style.color = $colorPalette
          .find('input[name="palette[titleslogan]"]')
          .val();
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
