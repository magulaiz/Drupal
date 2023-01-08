<?php

namespace Drupal\entity_test;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

/**
 * Defines the node schema handler.
 */
class EntityTestStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    $additional_indexes = \Drupal::state()->get($entity_type->id() . '.additional_indexes', []);
    foreach ($additional_indexes as $table => $indexes) {
      $schema[$table]['indexes'] = array_merge($schema[$table]['indexes'], $indexes);
    }

    return $schema;
  }

}
