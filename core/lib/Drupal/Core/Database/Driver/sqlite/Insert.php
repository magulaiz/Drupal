<?php

namespace Drupal\Core\Database\Driver\sqlite;

use Drupal\sqlite\Driver\Database\sqlite\Connection;
use Drupal\sqlite\Driver\Database\sqlite\Insert as SqliteInsert;

/**
 * SQLite implementation of \Drupal\Core\Database\Query\Insert.
 *
 * @deprecated in drupal:9.4.0 and is removed from drupal:11.0.0. The SQLite
 *   database driver has been moved to the sqlite module.
 *
 * @see https://www.drupal.org/node/3129492
 */
class Insert extends SqliteInsert {

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $connection, string $prefix) {
    @trigger_error('\Drupal\Core\Database\Driver\sqlite\Insert is deprecated in drupal:9.4.0 and is removed from drupal:11.0.0. The SQLite database driver has been moved to the sqlite module. See https://www.drupal.org/node/3129492', E_USER_DEPRECATED);
    parent::__construct($connection, $prefix);
  }

}
