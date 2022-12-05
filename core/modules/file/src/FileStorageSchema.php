<?php

namespace Drupal\file;

use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the file schema handler.
 */
class FileStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, $table_name, array $column_mapping) {
    $schema = parent::getSharedTableFieldSchema($storage_definition, $table_name, $column_mapping);
    $field_name = $storage_definition->getName();

    if ($table_name == $this->storage->getBaseTable()) {
      switch ($field_name) {
        case 'status':
        case 'changed':
        case 'uri':
          $this->addSharedTableFieldIndex($storage_definition, $schema);
          break;
      }
      // Entity keys automatically have not null assigned to TRUE, but for the
      // file entity, NULL is a valid value for uid.
      // @todo remove in Drupal 9.2.x after the entity type has been updated to
      // the new schema storage version. In order to ensure that we will remove
      // the code only after the entity type has been updated, the Drupal
      // version in which we are allowed to remove this code should not be
      // allowed to be updated to from Drupal versions older than 8.9.0.
      if ($field_name === 'uid') {
        $schema['fields']['uid']['not null'] = FALSE;
      }
    }

    return $schema;
  }

}
