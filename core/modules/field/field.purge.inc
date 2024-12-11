<?php

/**
 * @file
 */

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Purges a batch of deleted Field API data, field storages, or fields.
 *
 * This function will purge deleted field data in batches. The batch size
 * is defined as an argument to the function, and once each batch is finished,
 * it continues with the next batch until all have completed. If a deleted field
 * with no remaining data records is found, the field itself will
 * be purged. If a deleted field storage with no remaining fields is found, the
 * field storage itself will be purged.
 *
 * @param int $batch_size
 *   The maximum number of field data records to purge before returning.
 * @param string $field_storage_unique_id
 *   (optional) Limit the purge to a specific field storage. Defaults to NULL.
 *
 * @deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use the
 * @see https://www.drupal.org/project/drupal/issues/2907780
 */
function field_purge_batch($batch_size, $field_storage_unique_id = NULL) {
  \Drupal::service('entity_field.purgatory')->purgeBatch($batch_size, $field_storage_unique_id);
}

/**
 * Purges a field record from the database.
 *
 * This function assumes all data for the field has already been purged and
 * should only be called by field_purge_batch().
 *
 * @param \Drupal\Core\Field\FieldDefinitionInterface $field
 *   The field to purge.
 *
 * @deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use the
 * @see https://www.drupal.org/project/drupal/issues/2907780
 */
function field_purge_field(FieldDefinitionInterface $field) {
  \Drupal::service('entity_field.purgatory')->purgeFieldDefinition($field);
}

/**
 * Purges a field record from the database.
 *
 * This function assumes all fields for the field storage has already been
 * purged, and should only be called by field_purge_batch().
 *
 * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $field_storage
 *   The field storage to purge.
 *
 * @throws \Drupal\Core\Field\FieldException
 *
 * @deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use the
 * @see https://www.drupal.org/project/drupal/issues/2907780
 */
function field_purge_field_storage(FieldStorageDefinitionInterface $field_storage) {
  \Drupal::service('entity_field.purgatory')->purgeFieldStorageDefinition($field_storage);
}
