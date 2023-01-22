<?php

namespace Drupal\Core\Database\Driver\pgsql;

use Drupal\pgsql\Driver\Database\pgsql\Connection;
use Drupal\pgsql\Driver\Database\pgsql\Select as PgsqlSelect;

/**
 * PostgreSQL implementation of \Drupal\Core\Database\Query\Select.
 *
 * @deprecated in drupal:9.4.0 and is removed from drupal:11.0.0. The PostgreSQL
 *   database driver has been moved to the pgsql module.
 *
 * @see https://www.drupal.org/node/3129492
 */
class Select extends PgsqlSelect {

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $connection, $table, $alias = NULL, array $options = []) {
    @trigger_error('\Drupal\Core\Database\Driver\pgsql\Select is deprecated in drupal:9.4.0 and is removed from drupal:11.0.0. The PostgreSQL database driver has been moved to the pgsql module. See https://www.drupal.org/node/3129492', E_USER_DEPRECATED);
    parent::__construct($connection, $table, $alias, $options);
  }

}
