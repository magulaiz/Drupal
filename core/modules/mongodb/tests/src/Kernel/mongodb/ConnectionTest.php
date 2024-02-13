<?php

namespace Drupal\Tests\mongodb\Kernel\mongodb;

use Drupal\KernelTests\Core\Database\DriverSpecificDatabaseTestBase;

/**
 * MongoDB-specific connection tests.
 *
 * @group Database
 */
class ConnectionTest extends DriverSpecificDatabaseTestBase {

  /**
   * Tests deprecation of ::makeSequenceName().
   *
   * @group legacy
   */
  public function testMakeSequenceNameDeprecation(): void {
    $this->expectDeprecation("Drupal\\Core\\Database\\Connection::makeSequenceName() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. There is no replacement. See https://www.drupal.org/node/3377046");
    $this->assertIsString($this->connection->makeSequenceName('foo', 'bar'));
  }

}
