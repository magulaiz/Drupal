<?php

namespace Drupal\Core\Database\Driver\sqlite;

use Drupal\Core\Database\Connection;
use Drupal\sqlite\Driver\Database\sqlite\Statement as SqliteStatement;

/**
 * SQLite implementation of \Drupal\Core\Database\Statement.
 *
 * @deprecated in drupal:9.4.0 and is removed from drupal:11.0.0. The SQLite
 *   database driver has been moved to the sqlite module.
 *
 * @see https://www.drupal.org/node/3129492
 */
class Statement extends SqliteStatement {

  /**
   * {@inheritdoc}
   */
  public function __construct(\PDO $pdo_connection, Connection $connection, $query, array $driver_options = [], bool $row_count_enabled = FALSE) {
    @trigger_error('\Drupal\Core\Database\Driver\sqlite\Statement is deprecated in drupal:9.4.0 and is removed from drupal:11.0.0. The SQLite database driver has been moved to the sqlite module. See https://www.drupal.org/node/3129492', E_USER_DEPRECATED);
    parent::__construct($pdo_connection, $connection, $query, $driver_options, $row_count_enabled);
  }

}
