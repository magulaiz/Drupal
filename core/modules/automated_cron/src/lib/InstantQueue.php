<?php

declare(strict_types=1);

namespace Drupal\automated_cron\lib;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Utility\Environment;
use Drupal\Component\Utility\Random;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerManagerInterface;
use Psr\Log\LoggerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Queue\QueueProcessTrait;
use Drupal\Core\Utility\Error;

/**
 * Instant queue implementation.
 */
class InstantQueue {
  use QueueProcessTrait;

  /**
   * The cron configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected Config $config;

  /**
   * Array of queues names.
   *
   * @var array
   */
  protected array $queueNames = [];

  /**
   * Constructs a cron object.
   *
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Queue\QueueWorkerManagerInterface $queue_manager
   *   The queue plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param array $queue_config
   *   Queue configuration from the service container.
   */
  public function __construct(QueueFactory $queue_factory, LoggerInterface $logger, QueueWorkerManagerInterface $queue_manager, ConfigFactoryInterface $config_factory, protected Connection $connection, TimeInterface $time, array $queue_config) {
    $this->queueFactory = $queue_factory;
    $this->logger = $logger;
    $this->queueManager = $queue_manager;
    $this->time = $time;
    $this->queueConfig = $queue_config + [
      'suspendMaximumWait' => 30.0,
    ];
    $this->config = $config_factory->get('automated_cron.settings');
  }

  /**
   * Adds the queue name to the instantQueue for processing.
   *
   * @param string $name
   *   Name of the queue.
   *
   * @return void
   */
  public function addToInstantQueue(string $name): void {
    $this->queueNames[$name] = ($this->queueNames[$name] ?? 0) + 1;
  }

  /**
   * Returns the queue names.
   *
   * @return array
   */
  public function getQueueNames(): array {
    return $this->queueNames;
  }

  /**
   * Process the queue.
   *
   * @param array $queues_to_process
   *   Queues to process.
   * @param int $max_items_to_process
   *   Maximum number of items to process per queue in a run.
   */
  public function instantProcessQueues(array $queues_to_process, int $max_items_to_process): void {
    $max_process = $this->config->get('max_concurrent_queue_process');
    $query = $this->connection->select('semaphore', 's');
    $query->addExpression('count(*)', 'cnt');
    $current_count = (int) $query->condition('value', 'instant_queue_process')
      ->execute()->fetchField();

    if ($current_count < $max_process) {
      foreach ($queues_to_process as $queue_name => $no_items) {
        $queues_to_process[$queue_name] = $no_items > $max_items_to_process ? $max_items_to_process : $no_items;
      }

      $random = 'instant_queue_process:' . (new Random())->machineName();
      try {
        $this->connection->insert('semaphore')
          ->fields([
            'name' => $random,
            'value' => 'instant_queue_process',
            'expire' => strtotime('+1 hour'),
          ])
          ->execute();

        Environment::setTimeLimit(0);
        $this->processQueues($queues_to_process);

        $this->connection->delete('semaphore')
          ->condition('value', 'instant_queue_process')
          ->condition('name', $random)
          ->execute();
      }
      catch (\Exception $e) {
        Error::logException($this->logger, $e);
      }
    }
  }

}
