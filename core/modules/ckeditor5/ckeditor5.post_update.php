<?php

/**
 * @file
 * Post update functions for CKEditor 5.
 */

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\editor\Entity\Editor;

/**
 * Updates if an already migrated CKEditor 5 configuration for text formats
 * has alignment shown as individual buttons instead of a dropdown.
 */
function ckeditor5_post_update_alignment_buttons(&$sandbox = []) {
  $config_entity_updater = \Drupal::classResolver(ConfigEntityUpdater::class);

  $callback = function (Editor $editor) {
    // Only try to update editors using CKEditor 5.
    if ($editor->getEditor() !== 'ckeditor5') {
      return FALSE;
    }

    $needs_update = FALSE;
    // Only update if the editor is using the non-dropdown buttons.
    $settings = $editor->getSettings();
    $old_alignment_buttons_to_types = [
      'alignment:left' => 'left',
      'alignment:right' => 'right',
      'alignment:center' => 'center',
      'alignment:justify' => 'justify',
    ];
    if (is_array($settings['toolbar']['items'])) {
      foreach ($old_alignment_buttons_to_types as $button => $type) {
        if (in_array($button, $settings['toolbar']['items'], TRUE)) {
          $settings['toolbar']['items'] = array_values(array_diff($settings['toolbar']['items'], [$button]));
          $settings['plugins']['ckeditor5_alignment']['enabled_alignments'][] = $type;
          if (!in_array('alignment', $settings['toolbar']['items'], TRUE)) {
            $settings['toolbar']['items'][] = 'alignment';
          }
          // Flag this display as needing to be updated.
          $needs_update = TRUE;
        }
      }
    }
    if ($needs_update) {
      $editor->setSettings($settings);
    }
    return $needs_update;
  };

  $config_entity_updater->update($sandbox, 'editor', $callback);
}

/**
 * The image toolbar item changed from `uploadImage` to `drupalInsertImage`.
 *
 * Also, `uploadImage` always allowed all of the following attributes on <img>:
 * - `src`
 * - `alt`
 * - `data-entity-uuid`
 * - `data-entity-type`
 * - `height`
 * - `width`
 *
 * For `drupalInsertImage`, only `src`, `alt`, `width`, `height` are always
 * needed.
 * `data-entity-uuid` and `data-entity-type` are only needed if image uploads
 * are enabled. To ensure the same HTML remains editable (as well as to ensure
 * the HTML allowed by the `filter_html` filter still matches the CKEditor 5
 * configuration), the `ckeditor5_sourceEditing` plugin must be used to allow
 * `<img data-entity-uuid data-entity-type>` if image uploads are disabled.
 */
function ckeditor5_post_update_image_toolbar_item(&$sandbox = []) {
  $config_entity_updater = \Drupal::classResolver(ConfigEntityUpdater::class);

  $callback = function (Editor $editor) {
    // Only try to update editors using CKEditor 5.
    if ($editor->getEditor() !== 'ckeditor5') {
      return FALSE;
    }

    $needs_update = FALSE;
    // Only update if the editor is using the `uploadImage` toolbar item.
    $settings = $editor->getSettings();
    if (is_array($settings['toolbar']['items']) && in_array('uploadImage', $settings['toolbar']['items'], TRUE)) {
      // Replace `uploadImage` with `drupalInsertImage`.
      $settings['toolbar']['items'] = str_replace('uploadImage', 'drupalInsertImage', $settings['toolbar']['items']);
      // `<img data-entity-uuid data-entity-type>` (now only enabled when
      // uploads are enabled) must be explicitly added to the allowed tags for
      // `ckeditor5_sourceEditing` to avoid a BC break for editing pre-existing
      // content (the exact same HTML must remain editable).
      if (!$editor->getImageUploadSettings()['status']) {
        // Add `sourceEditing` toolbar item if it does not already exist.
        if (!in_array('sourceEditing', $settings['toolbar']['items'], TRUE)) {
          $settings['toolbar']['items'][] = '|';
          $settings['toolbar']['items'][] = 'sourceEditing';
          // @see \Drupal\ckeditor5\Plugin\CKEditor5Plugin\SourceEditing::defaultConfiguration()
          $settings['plugins']['ckeditor5_sourceEditing'] = ['allowed_tags' => []];
        }
        // Update configuration.
        $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags'] = HTMLRestrictions::fromString(implode(' ', $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']))
          ->merge(HTMLRestrictions::fromString('<img data-entity-uuid data-entity-type>'))
          ->toCKEditor5ElementsArray();
      }
      $needs_update = TRUE;
    }
    if ($needs_update) {
      $editor->setSettings($settings);
    }
    return $needs_update;
  };

  $config_entity_updater->update($sandbox, 'editor', $callback);
}
