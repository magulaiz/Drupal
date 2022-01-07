<?php

/**
 * @file
 * Provides views data for the datetime_range module.
 */

use Drupal\field\FieldStorageConfigInterface;

/**
 * Implements hook_field_views_data().
 */
function datetime_range_field_views_data(FieldStorageConfigInterface $field_storage) {
  // Include datetime.views.inc file in order for helper function
  // datetime_type_field_views_data_helper() to be available.
  \Drupal::moduleHandler()->loadInclude('datetime', 'inc', 'datetime.views');

  // Get datetime field data for value and end_value.
  $data = datetime_type_field_views_data_helper($field_storage, [], 'value');
  $data = datetime_type_field_views_data_helper($field_storage, $data, 'end_value');

  // Add datetime_range filter for all date range fields.
  foreach ($data as $table_name => $table_data) {
    $group = $data[$table_name][$field_storage->getName() . '_' . 'value']['group'];
    $filter = $data[$table_name][$field_storage->getName() . '_' . 'value']['filter'];
    $filter['id'] = 'datetime_range';
    $data[$table_name][$field_storage->getName()]['filter'] = $filter;
  }

  return $data;
}
