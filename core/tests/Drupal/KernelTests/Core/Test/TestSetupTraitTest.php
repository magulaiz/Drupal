<?php

namespace Drupal\KernelTests\Core\Test;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\TestSetupTrait;

/**
 * Tests the TestSetupTrait trait.
 *
 * @coversDefaultClass \Drupal\Tests\TestSetupTrait
 * @group Testing
 *
 * Run in a separate process as this test involves Database statics and
 * environment variables.
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class TestSetupTraitTest extends KernelTestBase {

  use TestSetupTrait;

  /**
   * @covers ::getDatabaseConnection
   * @group legacy
   */
  public function testGetDatabaseConnection(): void {
    $this->expectDeprecation('Drupal\Tests\TestSetupTrait::getDatabaseConnection is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. There is no replacement. See https://www.drupal.org/node/3176816');
    $this->assertNotNull($this->getDatabaseConnection());
  }

}
