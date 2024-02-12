<?php

namespace Drupal\mongodb\KeyValueStore;

use Drupal\Core\KeyValueStore\KeyValueDatabaseFactory as CoreKeyValueDatabaseFactory;

/**
 * The MongoDB implementation of \Drupal\Core\KeyValueStore\KeyValueDatabaseFactory.
 */
class KeyValueDatabaseFactory extends CoreKeyValueDatabaseFactory {

  /**
   * {@inheritdoc}
   */
  public function get($collection) {
    return new DatabaseStorage($collection, $this->serializer, $this->connection);
  }

}
