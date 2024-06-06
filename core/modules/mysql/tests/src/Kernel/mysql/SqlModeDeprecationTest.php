<?php

declare(strict_types=1);

namespace Drupal\Tests\mysql\Kernel\mysql;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests whether a deprecation warning is thrown when sql_mode is used.
 *
 * @group Database
 */
class SqlModeDeprecationTest extends KernelTestBase {

  /**
   * Tests behavior when sql_mode is used.
   */
  public function testSqlModeDeprecated(): void {
    // Find the current SUT database driver from the connection info. If that
    // is not the one the test requires, skip before test database
    // initialization so to save cycles.
    $this->root = static::getDrupalRoot();
    $connectionInfo = $this->getDatabaseConnectionInfo();
    $test_class_parts = explode('\\', get_class($this));
    $expected_provider = $test_class_parts[2] ?? '';
    for ($i = 3; $i < count($test_class_parts); $i++) {
      if ($test_class_parts[$i] === 'Kernel') {
        $expected_driver = $test_class_parts[$i + 1] ?? '';
        break;
      }
    }
    if ($connectionInfo['default']['driver'] !== $expected_driver) {
      $this->markTestSkipped("This test only runs for the database driver '$expected_driver'. Current database driver is '{$connectionInfo['default']['driver']}'.");
    }

    // We expect that getConnection() should trigger a deprecation warning
    // when called, if 'sql_mode' is in use.
    $this->assertDeprecation('there should be a deprecation message hee');
    $this->connection = Database::getConnection();
  }

  /**
   * {@inheritdoc}
   */
  protected function getDatabaseConnectionInfo() {
    $info = parent::getDatabaseConnectionInfo();

    // Add an 'sql_mode' option to 'init_commands' in the connectionInfo.
    $info['default']['init_commands'] = ['sql_mode' => "SET sql_mode = 'ANSI,TRADITIONAL'"];

    return $info;
  }

}
