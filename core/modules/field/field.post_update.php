<?php

/**
 * @file
 * Post update functions for Field module.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;

/**
 * Implements hook_removed_post_updates().
 */
function field_removed_post_updates(): array {
  return [
    'field_post_update_save_custom_storage_property' => '9.0.0',
    'field_post_update_entity_reference_handler_setting' => '9.0.0',
    'field_post_update_email_widget_size_setting' => '9.0.0',
    'field_post_update_remove_handler_submit_setting' => '9.0.0',
  ];
}

/**
 * Recalculate entity form display dependencies.
 */
function field_post_update_recalculate_entity_form_display_dependencies(?array &$sandbox = NULL): void {
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, 'entity_form_display');
}

/**
 * Recalculate entity view display dependencies.
 */
function field_post_update_recalculate_entity_view_display_dependencies(?array &$sandbox = NULL): void {
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, 'entity_view_display');
}
