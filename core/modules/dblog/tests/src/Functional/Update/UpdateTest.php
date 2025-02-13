<?php

declare(strict_types=1);

namespace Drupal\Tests\dblog\Functional\Update;

use Drupal\Core\Database\Connection;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests update hooks.
 *
 * @group dblog
 */
class UpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The database connection.
   */
  protected Connection $connection;

  /**
   * The name of the test database.
   */
  protected string $databaseName;

  /**
   * The prefixed 'watchdog' table.
   */
  protected string $tableName;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    /** @var \Drupal\Core\Database\Connection $connection */
    $this->connection = \Drupal::service('database');
    if ($this->connection->databaseType() == 'pgsql') {
      $this->databaseName = 'public';
    }
    else {
      $this->databaseName = $this->connection->getConnectionOptions()['database'];
    }
    $this->tableName = ($this->connection->getConnectionOptions()['prefix'] ?? '') . 'watchdog';
  }

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles(): void {
    if (file_exists(DRUPAL_ROOT . '/core/modules/system/tests/fixtures/update/drupal-11.1.0.bare.standard.php.gz')) {
      $this->databaseDumpFiles = [
        DRUPAL_ROOT . '/core/modules/system/tests/fixtures/update/drupal-11.1.0.bare.standard.php.gz',
      ];
    }
    else {
      $this->databaseDumpFiles = [
        DRUPAL_ROOT . '/core/modules/system/tests/fixtures/update/drupal-10.4.0.bare.standard.php.gz',
      ];
    }
    $this->databaseDumpFiles[] = __DIR__ . '/../../../fixtures/update/update_111001.php';
    $this->databaseDumpFiles[] = __DIR__ . '/../../../fixtures/update/update_111002.php';
  }

  /**
   * @covers \entity_usage_update_111001
   * @see https://www.drupal.org/project/drupal/issues/3493583
   */
  public function testUpdate111001(): void {
    if (\Drupal::service('database')->databaseType() == 'sqlite') {
      $this->markTestSkipped('This test does not support the SQLite database driver.');
    }

    $this->assertColumnLength(128);
    $this->runUpdates();
    $this->assertColumnLength(255);
  }

  /**
   * Asserts the string entity ID columns max length.
   *
   * @param int $expected_length
   *   The expected max length.
   */
  protected function assertColumnLength(int $expected_length): void {
    $query = <<<QUERY
    SELECT character_maximum_length
    FROM information_schema.columns
    WHERE table_schema = '%s'
      AND table_name = '%s'
      AND column_name = '%s';
    QUERY;
    foreach (['target_id_string', 'source_id_string'] as $column) {
      $actual_length = $this->connection->query(sprintf($query, $this->databaseName, $this->tableName, $column))->fetchField();
      $this->assertEquals($expected_length, $actual_length);
    }
  }

}
