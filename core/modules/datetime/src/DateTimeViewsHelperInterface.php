<?php

declare(strict_types=1);

namespace Drupal\datetime;

use Drupal\field\FieldStorageConfigInterface;

/**
 * A helper for datetime fields integrating with views.
 */
interface DateTimeViewsHelperInterface {

  /**
   * Provides Views integration for any datetime-based fields.
   *
   * Overrides the default Views data for datetime-based fields, adding datetime
   * views plugins. Modules defining new datetime-based fields may use this
   * function to simplify Views integration.
   *
   * @param \Drupal\field\FieldStorageConfigInterface $field_storage
   *   The field storage config entity.
   * @param array $data
   *   Field view data or views_field_default_views_data($field_storage) if empty.
   * @param string $column_name
   *   The schema column name with the datetime value.
   *
   * @return array
   *   The array of field views data with the datetime plugin.
   *
   * @see datetime_field_views_data()
   * @see datetime_range_field_views_data()
   */
  public function fieldViewsDataHelper(FieldStorageConfigInterface $field_storage, array $data, string $column_name): array;

}
