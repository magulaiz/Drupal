<?php

namespace Drupal\content_moderation;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the content moderation state schema handler.
 */
class ContentModerationStateStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    // Creates unique keys to guarantee the integrity of the entity and to make
    // the lookup in ModerationStateFieldItemList::getModerationState() fast.
    $unique_keys = [
      'content_entity_type_id',
      'content_entity_id',
      'content_entity_revision_id',
      'workflow',
      'langcode',
    ];
    if ($data_table = $this->storage->getDataTable()) {
      $schema[$data_table]['unique keys'] += [
        'content_moderation_state__lookup' => $unique_keys,
      ];
    }
    if ($revision_data_table = $this->storage->getRevisionDataTable()) {
      $schema[$revision_data_table]['unique keys'] += [
        'content_moderation_state__lookup' => $unique_keys,
      ];
    }

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, $table_name, array $column_mapping) {
    $schema = parent::getSharedTableFieldSchema($storage_definition, $table_name, $column_mapping);
    $field_name = $storage_definition->getName();

    if ($table_name == $this->storage->getRevisionDataTable()) {
      switch ($field_name) {
        // Add index to moderation state to improve performance for the
        // views plugins that join using this column.
        case 'moderation_state':
          $this->addSharedTableFieldIndex($storage_definition, $schema);
          break;
      }
    }

    return $schema;
  }

}
