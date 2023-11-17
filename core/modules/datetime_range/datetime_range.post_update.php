<?php

/**
 * @file
 * Post-update functions for Datetime Range module.
 */

use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;

/**
 * Implements hook_removed_post_updates().
 */
function datetime_range_removed_post_updates() {
  return [
    'datetime_range_post_update_translatable_separator' => '9.0.0',
    'datetime_range_post_update_views_string_plugin_id' => '9.0.0',
  ];
}

/**
 * Adds optional_values config in daterange field settings.
 */
function datetime_range_post_update_add_optional_values() {
  $config_factory = \Drupal::configFactory();
  foreach ($config_factory->listAll('field.field.') as $field_settings) {
    $field_config = $config_factory->getEditable($field_settings);
    if ($field_config->get('field_type') != 'daterange') {
      continue;
    }
    $settings = $field_config->get('settings');
    if (!isset($settings['optional_values'])) {
      $settings['optional_values'] = DateRangeItem::OPTIONAL_NONE;
      $field_config->set('settings', $settings);

      // Mark the resulting configuration as trusted data. This avoids issues with
      // future schema changes.
      $field_config->save(TRUE);
    }
  }
}
