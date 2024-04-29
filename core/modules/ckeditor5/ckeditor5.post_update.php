<?php

/**
 * @file
 * Post update functions for CKEditor 5.
 */

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\editor\Entity\Editor;
use Drupal\Core\Config\FileStorage;

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
    }
    if ($needs_update) {
      $editor->setSettings($settings);
    }
    return $needs_update;
  };

  $config_entity_updater->update($sandbox, 'editor', $callback);
}

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

/**
 * Creates the ckeditor_inline view mode for media entities.
 */
function ckeditor5_post_update_create_ckeditor_inline_view_mode() {
  $config_path = \Drupal::service('extension.list.module')->getPath('ckeditor5') . '/config/optional';
  $source = new FileStorage($config_path);
  $entity_type_manager = \Drupal::entityTypeManager();
  $module_handler = \Drupal::moduleHandler();
  /** @var \Drupal\Core\Config\StorageInterface $config_storage */
  $config_storage = \Drupal::service('config.storage');

  if ($module_handler->moduleExists('media') && !$config_storage->exists('core.entity_view_mode.media.ckeditor_inline')) {
    $entity_type_manager->getStorage('entity_view_mode')
      ->create($source->read('core.entity_view_mode.media.ckeditor_inline'))
      ->save();
  }
}

/**
 * Amends the filter format settings to allow the drupal-media-inline tag.
 */
function ckeditor5_post_update_allow_inline_media_element() {
  $formats = filter_formats();
  foreach ($formats as $format_id => $format) {
    $config = \Drupal::configFactory()->getEditable('filter.format.' . $format_id);
    $filters = $config->get('filters');
    if (\array_key_exists('media_embed', $filters)
      && \array_key_exists('filter_html', $filters)
      && !\str_contains($filters['filter_html']['settings']['allowed_html'], 'drupal-media-inline')) {
      $filters['filter_html']['settings']['allowed_html'] .= ' <drupal-media-inline data-entity-type data-entity-uuid alt data-view-mode data-caption data-align>';
      $config->set('filters', $filters);
      $config->save();
    }
  }
}
