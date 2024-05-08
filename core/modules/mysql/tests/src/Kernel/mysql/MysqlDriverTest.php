<?php

declare(strict_types=1);

namespace Drupal\Tests\mysql\Kernel\mysql;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\mysql\Driver\Database\mysql\Connection;
use Drupal\KernelTests\Core\Database\DriverSpecificKernelTestBase;
use Drupal\Tests\Core\Database\Stub\StubPDO;

/**
 * Tests the deprecations of the MySQL database driver classes in Core.
 *
 * @group Database
 */
#[CoversClass(\Drupal\mysql\Driver\Database\mysql\Connection::class)]
class MysqlDriverTest extends DriverSpecificKernelTestBase {

  public function testConnection() {
    $connection = new Connection($this->createMock(StubPDO::class), []);
    $this->assertInstanceOf(Connection::class, $connection);
  }

}
