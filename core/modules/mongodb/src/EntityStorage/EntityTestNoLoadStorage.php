<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\Core\Entity\EntityStorageException;

/**
 * The MongoDB implementation of \Drupal\entity_test\EntityTestNoLoadStorage.
 */
class EntityTestNoLoadStorage extends ContentEntityStorage {

  /**
   * {@inheritdoc}
   */
  public function load($id) {
    throw new EntityStorageException('No load operation is supposed to happen.');
  }

}
