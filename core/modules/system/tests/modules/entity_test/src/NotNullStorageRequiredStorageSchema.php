<?php

namespace Drupal\entity_test;

use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\RequiredFieldStorageDefinitionInterface;

/**
 * Defines a special schema handler for adding a NOT NULL constraints.
 *
 * If a field is storage required, then the NOT NULL constraint will be added.
 */
class NotNullStorageRequiredStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, $table_name, array $column_mapping) {
    $schema = parent::getSharedTableFieldSchema($storage_definition, $table_name, $column_mapping);

    if (($storage_definition instanceof RequiredFieldStorageDefinitionInterface) && $storage_definition->isStorageRequired()) {
      $field_name = $storage_definition->getName();
      $schema['fields'][$field_name]['not null'] = TRUE;
    }

    return $schema;
  }

}
