<?php

namespace Drupal\Core\Field;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Defines the field purgatory.
 */
class FieldPurgatory implements FieldPurgatoryInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The deleted fields repository.
   *
   * @var \Drupal\Core\Field\DeletedFieldsRepositoryInterface
   */
  protected $deletedFieldsRepository;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new field purgatory.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Field\DeletedFieldsRepositoryInterface $deleted_fields_repository
   *   The deleted fields repository.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, DeletedFieldsRepositoryInterface $deleted_fields_repository, ModuleHandlerInterface $module_handler, LoggerChannelFactoryInterface $logger_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->deletedFieldsRepository = $deleted_fields_repository;
    $this->moduleHandler = $module_handler;
    $this->logger = $logger_factory->get('field');
  }

  /**
   * {@inheritdoc}
   */
  public function purgeBatch($batch_size, $field_storage_unique_id = NULL) {
    $fields = $this->deletedFieldsRepository->getFieldDefinitions($field_storage_unique_id);

    $info = $this->entityTypeManager->getDefinitions();
    foreach ($fields as $field) {
      $entity_type = $field->getTargetEntityTypeId();

      // We cannot purge anything if the entity type is unknown (e.g. the
      // providing module was uninstalled).
      if (!isset($info[$entity_type])) {
        $this->logger->warning("Cannot remove field @field_name because the entity type is unknown: %entity_type",
        ['@field_name' => $field->getName(), '%entity_type' => $entity_type]);
        continue;
      }

      $count_purged = $this->entityTypeManager->getStorage($entity_type)->purgeFieldData($field, $batch_size);
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
  public function purgeFieldDefinition(FieldDefinitionInterface $field) {
    $this->deletedFieldsRepository->removeFieldDefinition($field);

    // Invoke external hooks after the cache is cleared for API consistency.
    $this->moduleHandler->invokeAll('field_purge_field', [$field]);
  }

  /**
   * {@inheritdoc}
   */
  public function purgeFieldStorageDefinition(FieldStorageDefinitionInterface $field_storage) {
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
