<?php

namespace Drupal\driver_test\Driver\Database\DrivertestMysql;

<<<<<<< HEAD
include_once dirname(__DIR__, 8) . '/mysql/src/Driver/Database/mysql/Connection.php';

=======
>>>>>>> upstream/11.x
use Drupal\mysql\Driver\Database\mysql\Connection as CoreConnection;

/**
 * MySQL test implementation of \Drupal\Core\Database\Connection.
 */
class Connection extends CoreConnection {

  /**
   * {@inheritdoc}
   */
  public function driver() {
    return 'DrivertestMysql';
  }

}
