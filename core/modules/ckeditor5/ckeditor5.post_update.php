<?php

/**
 * @file
 * Post update functions for CKEditor 5.
 */

use Drupal\Core\Config\FileStorage;

// cspell:ignore multiblock

/**
 * Implements hook_removed_post_updates().
 */
function ckeditor5_removed_post_updates() {
  return [
    'ckeditor5_post_update_alignment_buttons' => '10.0.0',
    'ckeditor5_post_update_image_toolbar_item' => '11.0.0',
    'ckeditor5_post_update_plugins_settings_export_order' => '11.0.0',
    'ckeditor5_post_update_code_block' => '11.0.0',
    'ckeditor5_post_update_list_multiblock' => '11.0.0',
    'ckeditor5_post_update_list_start_reversed' => '11.0.0',
  ];
}

/**
 * Creates the ckeditor_inline view mode for media entities.
 */
function ckeditor5_post_update_create_ckeditor_inline_view_mode() {
  $config_path = \Drupal::service('extension.list.module')->getPath('ckeditor5') . '/config/optional';
  $source = new FileStorage($config_path);
  $entity_type_manager = \Drupal::entityTypeManager();
  $module_handler = \Drupal::moduleHandler();

  if ($module_handler->moduleExists('media')) {
    $entity_type_manager->getStorage('entity_view_mode')
      ->create($source->read('core.entity_view_mode.media.ckeditor_inline'))
      ->save();
  }
  if ($module_handler->moduleExists('image')) {
    $entity_type_manager->getStorage('entity_view_display')
      ->create($source->read('core.entity_view_display.media.image.ckeditor_inline'))
      ->save();
  }
}
