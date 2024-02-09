<?php

/**
 * @file
 * Post update functions for Field module.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\Core\Field\FieldConfigBase;

/**
 * Converts empty `description` on fields to NULL.
 */
function field_post_update_set_empty_description_to_null(array &$sandbox): void {
  $callback = function (FieldConfigBase $entity): bool {
    if (trim($entity->getDescription()) === '') {
      $entity->set('description', NULL);
      return TRUE;
    }
    return FALSE;
  };
  /** @var \Drupal\Core\Config\Entity\ConfigEntityUpdater $updater */
  $updater = \Drupal::classResolver(ConfigEntityUpdater::class);
  $updater->update($sandbox, 'field_config', $callback);
  $updater->update($sandbox, 'base_field_override', $callback);
}

/**
 * Implements hook_removed_post_updates().
 */
function field_removed_post_updates() {
  return [
    'field_post_update_save_custom_storage_property' => '9.0.0',
    'field_post_update_entity_reference_handler_setting' => '9.0.0',
    'field_post_update_email_widget_size_setting' => '9.0.0',
    'field_post_update_remove_handler_submit_setting' => '9.0.0',
  ];
}
