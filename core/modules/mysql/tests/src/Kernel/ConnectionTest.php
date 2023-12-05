<?php

namespace Drupal\Tests\mysql\Kernel;

use Drupal\Core\Database\Database;
use Drupal\KernelTests\KernelTestBase;
use Drupal\mysql\Driver\Database\mysql\Connection;
use Drupal\mysql\Driver\Database\mysql\DatabaseTransactionIsolationLevelException;

/**
 * Tests the connection class.
 *
 * @coversDefaultClass \Drupal\mysql\Driver\Database\mysql\Connection
 *
 * @group Mysql
 */
class ConnectionTest extends KernelTestBase {

  /**
   * Tests transaction isolation level when an invalid option is set.
   *
   * @covers ::open
   */
  public function testInvalidIsolationLevelOption() {
    $connection_info = Database::getConnectionInfo();

    // The isolation_level option is only available for MySQL.
    if ($connection_info['default']['driver'] !== 'mysql') {
      $this->markTestSkipped("This test does not support the {$connection_info['default']['driver']} database driver.");
    }

    $connection_info['default']['isolation_level'] = 'INVALID_LEVEL';
    $this->expectException(DatabaseTransactionIsolationLevelException::class);
    $this->expectExceptionMessage("The isolation level INVALID_LEVEL is not valid, use one of options available instead.");

    Connection::open($connection_info['default']);
  }

}
