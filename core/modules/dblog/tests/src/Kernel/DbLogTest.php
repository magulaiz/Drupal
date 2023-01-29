<?php

namespace Drupal\Tests\dblog\Kernel;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Database\Database;
use Drupal\dblog\Entity\DblogEntry;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\dblog\Functional\FakeLogEntries;

/**
 * Generate events and verify dblog entries.
 *
 * @group dblog
 */
class DbLogTest extends KernelTestBase {

  use FakeLogEntries;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['dblog', 'system', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('dblog', ['watchdog']);
    $this->installSchema('system', ['sequences']);
    $this->installConfig(['system']);
  }

  /**
   * Tests that cron correctly applies the database log row limit.
   */
  public function testDbLogCron() {
    $row_limit = 100;
    // Generate additional log entries.
    $this->generateLogEntries($row_limit + 10);
    // Verify that the database log row count exceeds the row limit.
    $count = Database::getConnection()->select('watchdog')->countQuery()->execute()->fetchField();
    $this->assertGreaterThan($row_limit, $count, new FormattableMarkup('Dblog row count of @count exceeds row limit of @limit', ['@count' => $count, '@limit' => $row_limit]));

    // Get the number of enabled modules. Cron adds a log entry for each module.
    $implementation_count = 0;
    \Drupal::moduleHandler()->invokeAllWith(
      'cron',
      function (callable $hook, string $module) use (&$implementation_count) {
        $implementation_count++;
      }
    );

    $cron_detailed_count = $this->runCron();
    $this->assertEquals($implementation_count + 2, $cron_detailed_count, new FormattableMarkup('Cron added @count of @expected new log entries', ['@count' => $cron_detailed_count, '@expected' => $implementation_count + 2]));

    // Test disabling of detailed cron logging.
    $this->config('system.cron')->set('logging', 0)->save();
    $cron_count = $this->runCron();
    $this->assertEquals(1, $cron_count, new FormattableMarkup('Cron added @count of @expected new log entries', ['@count' => $cron_count, '@expected' => 1]));
  }

  /**
   * Runs cron and returns number of new log entries.
   *
   * @return int
   *   Number of new watchdog entries.
   */
  private function runCron() {
    $connection = Database::getConnection();
    // Get last ID to compare against; log entries get deleted, so we can't
    // reliably add the number of newly created log entries to the current count
    // to measure number of log entries created by cron.
    $query = $connection->select('watchdog');
    $query->addExpression('MAX([wid])');
    $last_id = $query->execute()->fetchField();

    // Run a cron job.
    $this->container->get('cron')->run();

    // Get last ID after cron was run.
    $query = $connection->select('watchdog');
    $query->addExpression('MAX([wid])');
    $current_id = $query->execute()->fetchField();

    return $current_id - $last_id;
  }

  /**
   * Tests serializing and unserializing against the `variables` field.
   */
  public function testSerialization() {
    $foo = new Foo();
    \Drupal::logger('dblog_test')->info('Foo %foo', [
      '%foo' => $foo,
    ]);
    $query = \Drupal::database()->select('watchdog');
    $query->addExpression('MAX([wid])');
    $last_id = $query->execute()->fetchField();
    $dblog = DblogEntry::load($last_id);
    $variables = unserialize($dblog->get('variables')->value);
    $this->assertEquals('Foo %foo', $dblog->get('message')->value);
    $this->assertEquals(3, $variables['%foo']->public);
  }

}

/**
 * A Foo class for testing.
 */
class Foo {

  /**
   * A private property.
   *
   * @var int
   */
  private $private = 1;

  /**
   * A protected property.
   *
   * @var int
   */
  protected $protected = 2;

  /**
   * A public property.
   *
   * @var int
   */
  public $public = 3;

}
