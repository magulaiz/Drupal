<?php

namespace Drupal\block_content;

use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the block content schema handler.
 */
class BlockContentStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, $table_name, array $column_mapping) {
    $schema = parent::getSharedTableFieldSchema($storage_definition, $table_name, $column_mapping);
    $field_name = $storage_definition->getName();

    if ($table_name == $this->storage->getDataTable()) {
      // Add index to moderation state to improve performance for the
      // views plugins that join using this column.
      if ($field_name === 'reusable') {
        $this->addSharedTableFieldIndex($storage_definition, $schema);
      }
    }

    return $schema;
  }

}
