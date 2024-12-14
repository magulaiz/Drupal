<?php

declare(strict_types=1);

namespace Drupal\Core\Field;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Defines the field purger.
 */
class FieldPurger implements FieldPurgerInterface {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected DeletedFieldsRepositoryInterface $deletedFieldsRepository,
    protected ModuleHandlerInterface $moduleHandler,
    protected LoggerChannelFactoryInterface $loggerFactory,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function purgeBatch($batch_size, $field_storage_unique_id = NULL): void {
    $fields = $this->deletedFieldsRepository->getFieldDefinitions($field_storage_unique_id);

    $info = $this->entityTypeManager->getDefinitions();
    foreach ($fields as $field) {
      $entity_type_id = $field->getTargetEntityTypeId();

      // We cannot purge anything if the entity type is unknown (e.g. the
      // providing module was uninstalled).
      if (!isset($info[$entity_type_id])) {
        $this->loggerFactory->get('field')->warning('Cannot remove field @field_name because the entity type is unknown: %entity_type_id', [
          '@field_name' => $field->getName(),
          '%entity_type_id' => $entity_type_id,
        ]);
        continue;
      }

      $count_purged = $this->entityTypeManager->getStorage($entity_type_id)->purgeFieldData($field, $batch_size);
      if ($count_purged < $batch_size || $count_purged == 0) {
        // No field data remains for the field, so we can remove it.
        $this->purgeFieldDefinition($field);
      }
      $batch_size -= $count_purged;
      // Only delete up to the maximum number of records.
      if ($batch_size == 0) {
        break;
      }
    }

    // Retrieve all deleted field storage definitions. Any that have no fields
    // can be purged.
    foreach ($this->deletedFieldsRepository->getFieldStorageDefinitions() as $field_storage) {
      if ($field_storage_unique_id && $field_storage->getUniqueStorageIdentifier() != $field_storage_unique_id) {
        // If a specific UUID is provided, only purge the corresponding field.
        continue;
      }

      // We cannot purge anything if the entity type is unknown (e.g. the
      // providing module was uninstalled).
      if (!isset($info[$field_storage->getTargetEntityTypeId()])) {
        continue;
      }

      $fields = $this->deletedFieldsRepository->getFieldDefinitions($field_storage->getUniqueStorageIdentifier());
      if (empty($fields)) {
        $this->purgeFieldStorageDefinition($field_storage);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function purgeFieldDefinition(FieldDefinitionInterface $field): void {
    $this->deletedFieldsRepository->removeFieldDefinition($field);

    // Invoke external hooks after the cache is cleared for API consistency.
    $this->moduleHandler->invokeAll('field_purge_field', [$field]);
  }

  /**
   * {@inheritdoc}
   */
  public function purgeFieldStorageDefinition(FieldStorageDefinitionInterface $field_storage): void {
    $fields = $this->deletedFieldsRepository->getFieldDefinitions($field_storage->getUniqueStorageIdentifier());
    if (count($fields) > 0) {
      throw new FieldException("Attempt to purge a field storage {$field_storage->getName()} that still has fields.");
    }

    $this->deletedFieldsRepository->removeFieldStorageDefinition($field_storage);

    // Notify the storage layer.
    $this->entityTypeManager->getStorage($field_storage->getTargetEntityTypeId())->finalizePurge($field_storage);

    // Invoke external hooks after the cache is cleared for API consistency.
    $this->moduleHandler->invokeAll('field_purge_field_storage', [$field_storage]);
  }

}
