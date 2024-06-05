<?php

declare(strict_types=1);

namespace Drupal\Tests\automated_cron\Functional;

use Drupal\Core\Database\Database;
use Drupal\cron_queue_test\Plugin\QueueWorker\CronQueueTestDeriverQueue;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\Traits\Core\CronRunTrait;

/**
 * Generic module test for automated_cron.
 *
 * @group automated_cron
 */
class InstantQueueTest extends BrowserTestBase {

  use CronRunTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['automated_cron', 'cron_queue_test', 'dblog'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    \Drupal::configFactory()->getEditable('automated_cron.settings')
      ->set('max_items_to_process', 10)
      ->set('max_concurrent_queue_process', 1)
      ->save();
  }

  /**
   * Tests that instant cron queues processes immediately.
   */
  public function testInstantQueue(): void {
    $result = $this->drupalGet('/instant-queue-test/10');
    $this->assertEquals(['status' => TRUE], json_decode($result, TRUE));
    sleep(1);
    $this->assertEquals(10, $this->getWatchdogCount());
    $this->assertEquals(0, $this->getQueueCount());
  }

  /**
   * Tests that only the queues to which the items were added are processed.
   */
  public function testInstantQueueOnlyAddedQueues(): void {
    $queue = \Drupal::queue(sprintf('%s:foo', CronQueueTestDeriverQueue::PLUGIN_ID));
    $queue->createItem('foo');
    $result = $this->drupalGet('/instant-queue-test/10');
    $this->assertEquals(['status' => TRUE], json_decode($result, TRUE));
    sleep(1);

    $this->assertEquals(10, $this->getWatchdogCount());
    $this->assertEquals(1, $this->getQueueCount());
  }

  /**
   * Tests that only the queues to which the items were added are processed.
   */
  public function testInstantQueueMaxItems(): void {
    $result = $this->drupalGet('/instant-queue-test/21');
    $this->assertEquals(['status' => TRUE], json_decode($result, TRUE));
    sleep(1);

    $this->assertEquals(10, $this->getWatchdogCount());
    $this->assertEquals(11, $this->getQueueCount());
  }

  /**
   * Tests max concurrent processes.
   */
  public function testInstantQueueMaxConcurrentProcesses(): void {
    \Drupal::configFactory()->getEditable('automated_cron.settings')
      ->set('max_items_to_process', 500)
      ->set('max_concurrent_queue_process', 2)
      ->save();

    $this->drupalGet('/instant-queue-test/500');
    $this->drupalGet('/instant-queue-test/500');

    sleep(1);

    $this->assertEquals(1000, $this->getWatchdogCount());
    $this->assertEquals(0, $this->getQueueCount());
  }

  /**
   * Tests instant queue does not process when max_items_to_process is zero.
   */
  public function testInstantQueueDoesNotProcess(): void {
    \Drupal::configFactory()->getEditable('automated_cron.settings')
      ->set('max_items_to_process', 0)
      ->set('max_concurrent_queue_process', 1)
      ->save();

    $result = $this->drupalGet('/instant-queue-test/500');

    sleep(1);

    $this->assertEquals(0, $this->getWatchdogCount());
    $this->assertEquals(500, $this->getQueueCount());

    // Run cron. All items should be processed.
    $this->cronRun();

    $this->assertEquals(500, $this->getWatchdogCount());
    $this->assertEquals(0, $this->getQueueCount());
  }

  /**
   * Returns watchdog count.
   *
   * @return int
   *   Number of entries in watchdog.
   */
  protected function getWatchdogCount(): int {
    $query = Database::getConnection()->select('watchdog', 's');
    $query->addExpression('count(*)', 'item_count');
    $query->condition('type', 'instant_queue');
    $result = $query->execute()->fetchField();
    return empty($result) ? 0 : (int) $result;
  }

  /**
   * Returns queue count.
   *
   * @return int
   *   Number of entries in queue.
   */
  protected function getQueueCount(): int {
    $query = Database::getConnection()->select('queue', 'q');
    $query->addExpression('count(*)', 'item_count');
    $result = $query->execute()->fetchField();
    return empty($result) ? 0 : (int) $result;
  }

}
