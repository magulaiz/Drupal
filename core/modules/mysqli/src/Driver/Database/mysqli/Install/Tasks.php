<?php

namespace Drupal\mysqli\Driver\Database\mysqli\Install;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\DatabaseNotFoundException;
use Drupal\mysql\Driver\Database\mysql\Install\Tasks as BaseInstallTasks;
use Drupal\mysqli\Driver\Database\mysqli\Connection;

/**
 * Specifies installation tasks for MySQLi.
 */
class Tasks extends BaseInstallTasks {

  /**
   * {@inheritdoc}
   */
  public function installable() {
    return extension_loaded('mysqli');
  }

  /**
   * {@inheritdoc}
   */
  public function name() {
    return $this->t('@parent via mysqli', ['@parent' => parent::name()]);
  }

}
