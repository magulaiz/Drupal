<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Helper class for entity update testing.
 *
 * @see \Drupal\KernelTests\Core\Entity\FieldableEntityDefinitionUpdateTest::testFieldableEntityTypeUpdatesErrorHandling()
 */
class EntityTestUpdateStorage extends ContentEntityStorage {

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

}
