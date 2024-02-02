<?php

/**
 * @file
 * Post update functions for CKEditor 5.
 */

<<<<<<< HEAD
use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\editor\Entity\Editor;

/**
 * Updates if an already migrated CKEditor 5 configuration for text formats
 * has alignment shown as individual buttons instead of a dropdown.
 */
function ckeditor5_post_update_alignment_buttons(&$sandbox = []) {
=======
use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\editor\Entity\Editor;

// cspell:ignore multiblock

/**
 * Implements hook_removed_post_updates().
 */
function ckeditor5_removed_post_updates() {
  return [
    'ckeditor5_post_update_alignment_buttons' => '10.0.0',
  ];
}

/**
 * The image toolbar item changed from `uploadImage` to `drupalInsertImage`.
 */
function ckeditor5_post_update_image_toolbar_item(&$sandbox = []) {
>>>>>>> upstream/11.x
  $config_entity_updater = \Drupal::classResolver(ConfigEntityUpdater::class);

  $callback = function (Editor $editor) {
    // Only try to update editors using CKEditor 5.
    if ($editor->getEditor() !== 'ckeditor5') {
      return FALSE;
    }

    $needs_update = FALSE;
<<<<<<< HEAD
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
=======
    // Only update if the editor is using the `uploadImage` toolbar item.
    $settings = $editor->getSettings();
    if (is_array($settings['toolbar']['items']) && in_array('uploadImage', $settings['toolbar']['items'], TRUE)) {
      // Replace `uploadImage` with `drupalInsertImage`.
      $settings['toolbar']['items'] = str_replace('uploadImage', 'drupalInsertImage', $settings['toolbar']['items']);
      // `<img data-entity-uuid data-entity-type>` are implicitly supported when
      // uploads are enabled as the attributes are necessary for upload
      // functionality. If uploads aren't enabled, these attributes must still
      // be supported to ensure existing content that may have them (despite
      // uploads being disabled) remains editable. In this use case, the
      // attributes are added to the `ckeditor5_sourceEditing` allowed tags.
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
>>>>>>> upstream/11.x
    }
    if ($needs_update) {
      $editor->setSettings($settings);
    }
    return $needs_update;
  };

  $config_entity_updater->update($sandbox, 'editor', $callback);
}
<<<<<<< HEAD
=======

/**
 * Updates Text Editors using CKEditor 5 to sort plugin settings by plugin key.
 */
function ckeditor5_post_update_plugins_settings_export_order(&$sandbox = []) {
  $config_entity_updater = \Drupal::classResolver(ConfigEntityUpdater::class);
  $config_entity_updater->update($sandbox, 'editor', function (Editor $editor): bool {
    // Only try to update editors using CKEditor 5.
    if ($editor->getEditor() !== 'ckeditor5') {
      return FALSE;
    }

    $settings = $editor->getSettings();

    // Nothing to do if there are fewer than two plugins with settings.
    if (count($settings['plugins']) < 2) {
      return FALSE;
    }
    ksort($settings['plugins']);
    $editor->setSettings($settings);

    return TRUE;
  });
}

/**
 * Updates Text Editors using CKEditor 5 Code Block.
 */
function ckeditor5_post_update_code_block(&$sandbox = []) {
  $config_entity_updater = \Drupal::classResolver(ConfigEntityUpdater::class);
  $config_entity_updater->update($sandbox, 'editor', function (Editor $editor): bool {
    // Only try to update editors using CKEditor 5.
    if ($editor->getEditor() !== 'ckeditor5') {
      return FALSE;
    }
    $settings = $editor->getSettings();
    // @see ckeditor5_editor_presave()
    return in_array('codeBlock', $settings['toolbar']['items'], TRUE);
  });
}

/**
 * Updates Text Editors using CKEditor 5.
 */
function ckeditor5_post_update_list_multiblock(&$sandbox = []) {
  $config_entity_updater = \Drupal::classResolver(ConfigEntityUpdater::class);
  $config_entity_updater->update($sandbox, 'editor', function (Editor $editor): bool {
    // Only try to update editors using CKEditor 5.
    if ($editor->getEditor() !== 'ckeditor5') {
      return FALSE;
    }
    $settings = $editor->getSettings();

    // @see ckeditor5_editor_presave()
    return array_key_exists('ckeditor5_list', $settings['plugins']);
  });
}

/**
 * Updates Text Editors using CKEditor 5 to native List "start" functionality.
 */
function ckeditor5_post_update_list_start_reversed(&$sandbox = []) {
  $config_entity_updater = \Drupal::classResolver(ConfigEntityUpdater::class);
  $config_entity_updater->update($sandbox, 'editor', function (Editor $editor): bool {
    // Only try to update editors using CKEditor 5.
    if ($editor->getEditor() !== 'ckeditor5') {
      return FALSE;
    }
    $settings = $editor->getSettings();

    // @see ckeditor5_editor_presave()
    return in_array('numberedList', $settings['toolbar']['items'], TRUE)
      && array_key_exists('ckeditor5_sourceEditing', $settings['plugins']);
  });
}
>>>>>>> upstream/11.x
