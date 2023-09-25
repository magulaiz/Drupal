<?php

/**
 * @file
 * Post-update functions for Datetime Range module.
 */

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
 * Adds optional_end_date config in daterange field storage settings.
 */
function datetime_range_post_update_add_optional_end_date() {
  $config_factory = \Drupal::configFactory();
  foreach ($config_factory->listAll('field.storage.') as $field_storage) {
    $field_storage_settings = $config_factory->getEditable($field_storage);
    if ($field_storage_settings->get('type') != 'daterange') {
      continue;
    }
    $settings = $field_storage_settings->get('settings');
    $settings['optional_end_date'] = FALSE;
    $field_storage_settings->set('settings', $settings);

    // Mark the resulting configuration as trusted data. This avoids issues with
    // future schema changes.
    $field_storage_settings->save(TRUE);
  }
}
