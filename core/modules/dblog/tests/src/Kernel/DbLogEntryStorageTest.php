<?php

namespace Drupal\Tests\dblog\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\dblog\Functional\FakeLogEntries;
use Drupal\Core\Logger\RfcLogLevel;

/**
 * Test the dblog entries storage.
 *
 * @group dblog
 */
class DbLogEntryStorageTest extends KernelTestBase {

  use FakeLogEntries;

  /**
   * The dblog storage.
   *
   * @var \Drupal\dblog\DblogEntryStorageInterface
   */
  protected $storage;

  /**
   * The dblog formatter service.
   *
   * @var \Drupal\dblog\DblogFormatterInterface
   */
  protected $dblogFormatter;

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
    $this->storage = \Drupal::entityTypeManager()->getStorage('dblog');
    $this->dblogFormatter = \Drupal::service('dblog.formatter');
  }

  /**
   * Test createInstance method.
   */
  public function testCreateInstance() {
    $this->assertEquals('dblog', $this->storage->getEntityTypeId());
    $this->assertEquals('Dblog entry', $this->storage->getEntityType()->getLabel());
  }

  /**
   * Test deleteAll and hasData methods.
   */
  public function testDeleteAll() {
    $this->generateLogEntries(5);
    $this->assertCount(5, $this->storage->loadMultiple());
    $this->assertTrue($this->storage->hasData());

    $this->storage->deleteAll();

    $this->assertFalse($this->storage->hasData());

    $this->generateLogEntries(5);
    // Delete all but the first log entry.
    $this->storage->deleteAll(1);
    $this->assertTrue($this->storage->hasData());
    $this->assertCount(1, $this->storage->loadMultiple());

    $this->expectException(\InvalidArgumentException::class);
    $this->storage->deleteAll(-1);
  }

  /**
   * Test messageType method.
   */
  public function testMessageTypes() {
    $this->assertEquals([], $this->storage->messageTypes());
    $this->generateLogEntries(5);
    $this->assertEquals(['custom' => 'custom'], $this->storage->messageTypes());

    $this->generateLogEntries(1, ['channel' => 'system']);
    $this->assertEquals([
      'custom' => 'custom',
      'system' => 'system',
    ], $this->storage->messageTypes());
  }

  /**
   * Test loadMultiple method.
   */
  public function testLoadMultiple() {
    $this->assertEquals([], $this->storage->loadMultiple());
    $this->assertEquals([], $this->storage->loadMultiple([12, 13, 14]));

    $this->generateLogEntries(3);
    $this->assertEquals([], $this->storage->loadMultiple([12, 13, 14]));

    $logs = $this->storage->loadMultiple();
    $this->assertCount(3, $logs);

    $third = array_pop($logs);
    $second = array_pop($logs);
    $first = array_pop($logs);

    $this->assertEquals('Dblog test log message Entry #2', $first->getFormattedMessage($this->dblogFormatter), 'Logs are loaded, most recent first');
    $this->assertEquals('Dblog test log message Entry #1', $second->getFormattedMessage($this->dblogFormatter), 'Logs are loaded, most recent first');
    $this->assertEquals('Dblog test log message Entry #0', $third->getFormattedMessage($this->dblogFormatter), 'Logs are loaded, most recent first');

    $logs = $this->storage->loadMultiple(
      [$second->id(), $third->id(), $first->id()]
    );

    $c = array_pop($logs);
    $b = array_pop($logs);
    $a = array_pop($logs);

    // Logs can be loaded in a particular order as well.
    $this->assertEquals('Dblog test log message Entry #1', $a->getFormattedMessage($this->dblogFormatter));
    $this->assertEquals('Dblog test log message Entry #0', $b->getFormattedMessage($this->dblogFormatter));
    $this->assertEquals('Dblog test log message Entry #2', $c->getFormattedMessage($this->dblogFormatter));

    $logs = $this->storage->loadMultiple([$a->id(), $a->id(), $a->id()]);
    $this->assertCount(1, $logs, 'Duplicated logs id are loaded once');
  }

  /**
   * Test load and loadUnchanged methods.
   */
  public function testLoad() {
    $this->generateLogEntries(3);
    $logs = $this->storage->loadMultiple();

    $third = array_pop($logs);
    $second = array_pop($logs);
    $first = array_pop($logs);

    $this->assertEquals($second, $this->storage->load($second->id()));
    $this->assertNull($this->storage->load(100));
    $this->assertEquals($second, $this->storage->loadUnchanged($second->id()));
  }

  /**
   * Test loadByProperties method.
   */
  public function testLoadByProperties() {
    $logger = \Drupal::service('logger.factory')->get('dblog');
    $logger->warning('/other', ['channel' => 'page not found']);
    $logger->error('Unexpected error', ['channel' => 'php']);
    $logger->error('Another unexpected error', ['channel' => 'php']);

    $logs = $this->storage->loadByProperties();
    $this->assertCount(3, $logs);

    $logs = $this->storage->loadByProperties(['severity' => RfcLogLevel::ERROR]);
    $this->assertCount(2, $logs);

    $logs = $this->storage->loadByProperties(['type' => 'php']);
    $this->assertCount(2, $logs);

    $logs = $this->storage->loadByProperties([
      'type' => 'php',
      'severity' => RfcLogLevel::WARNING,
    ]);
    $this->assertCount(0, $logs);

    $logs = $this->storage->loadByProperties([
      'type' => ['php', 'page not found'],
    ]);
    $this->assertCount(3, $logs);
  }

  /**
   * Test hasData method.
   */
  public function testHasData() {
    $this->assertFalse($this->storage->hasData());
    $this->generateLogEntries(1);
    $this->assertTrue($this->storage->hasData());
  }

  /**
   * Test aggregated query and get query methods.
   */
  public function testAggregateCount() {
    $this->generateLogEntries(2);

    $result = (int) $this->storage->getQuery()->count()->accessCheck(FALSE)->execute();
    $this->assertEquals(2, $result);

    $result = (int) $this->storage->getAggregateQuery()->count()->accessCheck(FALSE)->execute();
    $this->assertEquals(2, $result);
  }

  /**
   * Test mostFrequentLogEntries method.
   */
  public function testMostFrequentLogs() {
    $logger = \Drupal::service('logger.factory')->get('dblog');
    $context = ['channel' => 'page not found'];
    foreach (range(1, 5) as $i) {
      $logger->warning('/other', $context);

      if ($i < 3) {
        $logger->warning('/something_else', $context);
      }
    }
    $this->generateLogEntries(3, ['channel' => 'access denied']);

    $most_frequent = $this->storage->mostFrequentLogEntries('page not found');
    $this->assertCount(2, $most_frequent, 'There are 2 aggregated log entries for type: page not found');
    $this->assertEquals(5, $most_frequent[0]['count']);
    $this->assertEquals('/other', $most_frequent[0]['entry']->getFormattedMessage($this->dblogFormatter), 'Most frequent page not found log entry is: /other');
    $this->assertEquals(2, $most_frequent[1]['count']);
    $this->assertEquals('/something_else', $most_frequent[1]['entry']->getFormattedMessage($this->dblogFormatter));
  }

  /**
   * Test loadMostRecent method.
   */
  public function testLoadMostRecent() {
    $this->generateLogEntries(2, ['message' => 'First', 'channel' => 'node']);
    $this->generateLogEntries(4, ['message' => 'Second', 'channel' => 'user']);

    $most_recent = $this->storage->loadMostRecent();
    $this->assertEquals('Second Entry #3', $most_recent->getFormattedMessage($this->dblogFormatter));

    $most_recent = $this->storage->loadMostRecent(['type' => 'node']);
    $this->assertEquals('First Entry #1', $most_recent->getFormattedMessage($this->dblogFormatter));
  }

}
