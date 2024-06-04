<?php

declare(strict_types=1);

namespace Drupal\Tests\automated_cron\Functional;

use Drupal\Core\Database\Database;
use Drupal\cron_queue_test\Plugin\QueueWorker\CronQueueTestDeriverQueue;
use Drupal\Tests\BrowserTestBase;

/**
 * Generic module test for automated_cron.
 *
 * @group automated_cron
 */
class InstantQueueTest extends BrowserTestBase {

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
    $query = Database::getConnection()->select('watchdog', 's');
    $query->addExpression('count(*)', 'item_count');
    $query->condition('type', 'instant_queue');
    $result = $query->execute()->fetchField();
    $this->assertEquals(10, $result);
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

    $query = Database::getConnection()->select('watchdog', 'w');
    $query->addExpression('count(*)', 'item_count');
    $query->condition('type', 'instant_queue');
    $result = $query->execute()->fetchField();
    $this->assertEquals(10, $result);

    $query = Database::getConnection()->select('queue', 'q');
    $query->addExpression('count(*)', 'item_count');
    $foo_items = $query->execute()->fetchField();
    $this->assertEquals(1, $foo_items);
  }

  /**
   * Tests that only the queues to which the items were added are processed.
   */
  public function testInstantQueueMaxItems(): void {
    $result = $this->drupalGet('/instant-queue-test/21');
    $this->assertEquals(['status' => TRUE], json_decode($result, TRUE));
    sleep(1);

    $query = Database::getConnection()->select('watchdog', 'w');
    $query->addExpression('count(*)', 'item_count');
    $query->condition('type', 'instant_queue');
    $result = $query->execute()->fetchField();
    $this->assertEquals(10, $result);

    $query = Database::getConnection()->select('queue', 'q');
    $query->addExpression('count(*)', 'item_count');
    $foo_items = $query->execute()->fetchField();
    $this->assertEquals(11, $foo_items);
  }

  /**
   * Tests max concurrent processes is one.
   */
  public function testInstantQueueMaxConcurrentProcessesOne(): void {
    \Drupal::configFactory()->getEditable('automated_cron.settings')
      ->set('max_items_to_process', 500)
      ->set('max_concurrent_queue_process', 1)
      ->save();
    $result = $this->drupalGet('/instant-queue-test/500');
    $result = $this->drupalGet('/instant-queue-test/100');

    sleep(2);

    $query = Database::getConnection()->select('watchdog', 'w');
    $query->addExpression('count(*)', 'item_count');
    $query->condition('type', 'instant_queue');
    $result = $query->execute()->fetchField();
    $this->assertEquals(500, $result);

    $query = Database::getConnection()->select('queue', 'q');
    $query->addExpression('count(*)', 'item_count');
    $foo_items = $query->execute()->fetchField();
    $this->assertEquals(100, $foo_items);
  }

  /**
   * Tests max concurrent processes is one.
   */
  public function testInstantQueueMaxConcurrentProcessesTwo(): void {
    \Drupal::configFactory()->getEditable('automated_cron.settings')
      ->set('max_items_to_process', 500)
      ->set('max_concurrent_queue_process', 2)
      ->save();

    $result = $this->drupalGet('/instant-queue-test/500');
    $result = $this->drupalGet('/instant-queue-test/500');

    sleep(1);

    $query = Database::getConnection()->select('watchdog', 'w');
    $query->addExpression('count(*)', 'item_count');
    $query->condition('type', 'instant_queue');
    $result = $query->execute()->fetchField();
    $this->assertEquals(1000, $result);

    $query = Database::getConnection()->select('queue', 'q');
    $query->addExpression('count(*)', 'item_count');
    $foo_items = $query->execute()->fetchField();
    $this->assertEquals(0, $foo_items);
  }

}
