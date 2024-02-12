<?php

namespace Drupal\mongodb\KeyValueStore;

use Drupal\Core\KeyValueStore\KeyValueDatabaseExpirableFactory as CoreKeyValueDatabaseExpirableFactory;

/**
 * The MongoDB implementation of \Drupal\Core\KeyValueStore\KeyValueDatabaseExpirableFactory.
 */
class KeyValueDatabaseExpirableFactory extends CoreKeyValueDatabaseExpirableFactory {

  /**
   * {@inheritdoc}
   */
  public function get($collection) {
    if (!isset($this->storages[$collection])) {
      $this->storages[$collection] = new DatabaseStorageExpirable($collection, $this->serializer, $this->connection);
    }
    return $this->storages[$collection];
  }

}
