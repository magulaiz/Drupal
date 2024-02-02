<?php

namespace Drupal\driver_test\Driver\Database\DrivertestPgsql\Install;

<<<<<<< HEAD
include_once dirname(__DIR__, 9) . '/pgsql/src/Driver/Database/pgsql/Install/Tasks.php';

=======
>>>>>>> upstream/11.x
use Drupal\pgsql\Driver\Database\pgsql\Install\Tasks as CoreTasks;

/**
 * Specifies installation tasks for PostgreSQL databases.
 */
class Tasks extends CoreTasks {

  /**
   * {@inheritdoc}
   */
  public function name() {
    return t('PostgreSQL by the driver_test module');
  }

}
