<?php

declare(strict_types=1);

namespace Drupal\automated_cron\lib;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Utility\Environment;
use Drupal\Component\Utility\Random;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Cron as BaseCron;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerManagerInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\Core\State\StateInterface;
use Psr\Log\LoggerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Utility\Error;

/**
 * {@inheritdoc}
 */
class Cron extends BaseCron {

  /**
   * The cron configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The drupal database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a cron object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock service.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Session\AccountSwitcherInterface $account_switcher
   *   The account switching service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Queue\QueueWorkerManagerInterface $queue_manager
   *   The queue plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Database\Connection $connection
   *   The Connection object containing the key-value tables.
   * @param \Drupal\Component\Datetime\TimeInterface|null $time
   *   The time service.
   * @param mixed[]|null $queue_config
   *   Queue configuration from the service container.
   */
  public function __construct(ModuleHandlerInterface $module_handler, LockBackendInterface $lock, QueueFactory $queue_factory, StateInterface $state, AccountSwitcherInterface $account_switcher, LoggerInterface $logger, QueueWorkerManagerInterface $queue_manager, ConfigFactoryInterface $config_factory, Connection $connection, TimeInterface $time = NULL, ?array $queue_config = NULL) {
    parent::__construct($module_handler, $lock, $queue_factory, $state, $account_switcher, $logger, $queue_manager, $time, $queue_config);
    $this->config = $config_factory->get('automated_cron.settings');
    $this->connection = $connection;
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
