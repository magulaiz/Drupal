<?php

namespace Drupal\Core\Database\Driver\pgsql;

use Drupal\pgsql\Driver\Database\pgsql\Connection as PgsqlConnection;

/**
 * PostgreSQL implementation of \Drupal\Core\Database\Connection.
 *
 * @deprecated in drupal:9.4.0 and is removed from drupal:11.0.0. The PostgreSQL
 *   database driver has been moved to the pgsql module.
 *
 * @see https://www.drupal.org/node/3129492
 */
class Connection extends PgsqlConnection {

  /**
   * Constructs a Connection object.
   */
  public function __construct() {
    @trigger_error('\Drupal\Core\Database\Driver\pgsql\Connection is deprecated in drupal:9.4.0 and is removed from drupal:11.0.0. The PostgreSQL database driver has been moved to the pgsql module. See https://www.drupal.org/node/3129492', E_USER_DEPRECATED);
  }

}
