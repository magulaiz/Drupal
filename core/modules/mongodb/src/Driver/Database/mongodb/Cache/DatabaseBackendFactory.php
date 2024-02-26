<?php

namespace Drupal\mongodb\Driver\Database\mongodb\Cache;

use Drupal\Core\Cache\DatabaseBackendFactory as CoreDatabaseBackendFactory;

/**
 * The MongoDB implementation of \Drupal\Core\Cache\DatabaseBackendFactory.
 */
class DatabaseBackendFactory extends CoreDatabaseBackendFactory {

  /**
   * {@inheritdoc}
   */
  public function get($bin) {
    $max_rows = $this->getMaxRowsForBin($bin);
    // MongoDB needs it own version of \Drupal\mongodb\Cache\DatabaseBackend.
    return new DatabaseBackend($this->connection, $this->checksumProvider, $bin, $this->serializer, $this->time, $max_rows);
  }

}
