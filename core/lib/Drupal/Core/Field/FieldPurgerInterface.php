<?php

declare(strict_types=1);

namespace Drupal\Core\Field;

/**
 * Defines an interface for the happy place where fields go to rest in peace.
 *
 * @defgroup field_purge Field API bulk data deletion
 * @{
 * Cleans up after Field API bulk deletion operations.
 *
 * Field API provides functions for deleting data attached to individual
 * entities as well as deleting entire fields or field storages in a single
 * operation.
 *
 * When a single entity is deleted, the Entity storage performs the
 * following operations:
 * - Invoking the method \Drupal\Core\Field\FieldItemListInterface::delete() for
 *   each field on the entity. A file field type might use this method to delete
 *   uploaded files from the filesystem.
 * - Removing the data from storage.
 * - Invoking the global hook_entity_delete() for all modules that implement it.
 *   Each hook implementation receives the entity being deleted and can operate
 *   on whichever subset of the entity's bundle's fields it chooses to.
 *
 * Similar operations are performed on deletion of a single entity revision.
 *
 * When a bundle, field or field storage is deleted, it is not practical to
 * perform those operations immediately on every affected entity in a single
 * page request; there could be thousands or millions of them. Instead, the
 * appropriate field data items, fields, and/or field storages are marked as
 * deleted so that subsequent load or query operations will not return them.
 * Later, a separate process cleans up, or "purges", the marked-as-deleted data
 * by going through the three-step process described above and, finally,
 * removing deleted field storage and field records.
 *
 * Purging field data is made somewhat tricky by the fact that, while
 * $entity->delete() has a complete entity to pass to the various deletion
 * steps, the Field API purge process only has the field data it has previously
 * stored. It cannot reconstruct complete original entities to pass to the
 * deletion operations. It is even possible that the original entity to which
 * some Field API data was attached has been itself deleted before the field
 * purge operation takes place.
 *
 * Field API resolves this problem by using stub entities during purge
 * operations, containing only the information from the original entity that
 * Field API knows about: entity type, ID, revision ID, and bundle. It also
 * contains the field data for whichever field is currently being purged.
 *
 * See @link field Field API @endlink for information about the other parts of
 * the Field API.
 */
interface FieldPurgerInterface {

  /**
   * Purges a batch of deleted entity field data, field storages, or fields.
   *
   * This function will purge deleted field data in batches. The batch size is
   * defined as an argument to the function, and once each batch is finished, it
   * continues with the next batch until all have completed. If a deleted field
   * with no remaining data records is found, the field itself will be purged.
   * If a deleted field storage with no remaining fields is found, the field
   * storage itself will be purged.
   *
   * @param int $batch_size
   *   The maximum number of field data records to purge before returning.
   * @param string|null $field_storage_unique_id
   *   (optional) Limit the purge to a specific field storage. Defaults to NULL.
   */
  public function purgeBatch(int $batch_size, ?string $field_storage_unique_id = NULL): void;

  /**
   * Purges a field definition from the database.
   *
   * This function assumes all data for the field has already been purged and
   * should only be called by purgeBatch().
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field
   *   The field definition to purge.
   */
  public function purgeFieldDefinition(FieldDefinitionInterface $field): void;

  /**
   * Purges a field storage definition from the database.
   *
   * This function assumes all fields for the field storage has already been
   * purged, and should only be called by purgeBatch().
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $field_storage
   *   The field storage definition to purge.
   *
   * @throws \Drupal\Core\Field\FieldException
   */
  public function purgeFieldStorageDefinition(FieldStorageDefinitionInterface $field_storage): void;

}

/**
 * @} End of "defgroup field_purge".
 */
