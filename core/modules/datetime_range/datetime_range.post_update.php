<?php

/**
 * @file
 * Post-update functions for Datetime Range module.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\field\FieldConfigInterface;

/**
 * Implements hook_removed_post_updates().
 */
function datetime_range_removed_post_updates(): array {
  return [
    'datetime_range_post_update_translatable_separator' => '9.0.0',
    'datetime_range_post_update_views_string_plugin_id' => '9.0.0',
    'datetime_range_post_update_from_to_configuration' => '11.0.0',
  ];

}

/**
 * Adds optional_values config in daterange field settings.
 */
function datetime_range_post_update_add_optional_values(&$sandbox = NULL): void {
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, 'field_config', function (FieldConfigInterface $fieldConfig) {
    if ($fieldConfig->get('field_type') != 'daterange') {
      return FALSE;
    }
    $settings = $fieldConfig->get('settings');
    if (!isset($settings['optional'])) {
      return TRUE;
    }
    return FALSE;
  });
}
