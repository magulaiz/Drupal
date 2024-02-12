<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\Core\Entity\ContentEntityTypeInterface;

/**
 * Defines the path_alias schema handler.
 */
class PathAliasStorageSchema extends ContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    $schema[$this->storage->getBaseTable()]['indexes'] += [
      'path_alias__alias_langcode_id_status' => ['alias', 'langcode', 'id', 'status'],
      'path_alias__path_langcode_id_status' => ['path', 'langcode', 'id', 'status'],
    ];

    return $schema;
  }

}
