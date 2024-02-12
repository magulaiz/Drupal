<?php

namespace Drupal\mongodb\Cache;

use Drupal\Core\Cache\DatabaseBackendFactory as CoreDatabaseBackendFactory;

/**
 * The MongoDB implementation of \Drupal\Core\Cache\DatabaseBackendFactory.
 */
class DatabaseBackendFactory extends CoreDatabaseBackendFactory {

  /**
   * {@inheritdoc}
   */
  public function get($bin) {
    // MongoDB needs it own version of \Drupal\mongodb\Cache\DatabaseBackend.
    return new DatabaseBackend($this->connection, $this->checksumProvider, $bin);
  }

}
