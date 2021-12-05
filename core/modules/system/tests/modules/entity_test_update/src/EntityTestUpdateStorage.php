<?php

namespace Drupal\entity_test_update;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Helper class for entity update testing.
 *
 * @see \Drupal\KernelTests\Core\Entity\FieldableEntityDefinitionUpdateTest::testFieldableEntityTypeUpdatesErrorHandling()
 */
class EntityTestUpdateStorage extends SqlContentEntityStorage {

  /**
   * {@inheritdoc}
   */
  protected function saveToDedicatedTables(ContentEntityInterface $entity, $update = TRUE, $names = []) {
    // Simulate an error during the 'restore' process of a test entity.
    if (\Drupal::state()->get('entity_test_update.throw_exception', FALSE)) {
      throw new \Exception('Peekaboo!');
    }
    parent::saveToDedicatedTables($entity, $update, $names);
  }

  /**
   * {@inheritdoc}
   */
  protected function doSaveFieldItems(ContentEntityInterface $entity, array $names = []) {
    // Because the id column does not get serialized, the id might need to be counted up here
    if ($entity->id() === NULL) {
      $id_field = $entity->getEntityType()->getKey('id');
      $entity->set($id_field, $this->database->nextId($this->database->query('SELECT MAX(' . $id_field . ') FROM {' . $this->getBaseTable() . '}')->fetchField()));
      $entity->enforceIsNew();
    }
    return parent::doSaveFieldItems($entity, $names);
  }

  /**
   * {@inheritdoc}
   */
  protected function isColumnSerial($table_name, $schema_name) {
    // Allows us to save with id = 0, just like user entity type
    return $table_name == $this->revisionTable && $schema_name == $this->revisionKey;
  }

}
