<?php

namespace Drupal\Core;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Utility\Environment;
use Drupal\Component\Utility\Timer;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueProcessTrait;
use Drupal\Core\Queue\QueueWorkerManagerInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Utility\Error;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * The Drupal core Cron service.
 */
class Cron implements CronInterface {
  use QueueProcessTrait;

  /**
   * The queue config.
   *
   * @var array
   */
  protected array $queueConfig;

  /**
   * Constructs a cron object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock service.
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   *   The queue service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Session\AccountSwitcherInterface $accountSwitcher
   *   The account switching service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Queue\QueueWorkerManagerInterface $queueManager
   *   The queue plugin manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param array $queue_config
   *   Queue configuration from the service container.
   */
  public function __construct(
    protected ModuleHandlerInterface $moduleHandler,
    protected LockBackendInterface $lock,
    QueueFactory $queueFactory,
    protected StateInterface $state,
    protected AccountSwitcherInterface $accountSwitcher,
    LoggerInterface $logger,
    QueueWorkerManagerInterface $queueManager,
    TimeInterface $time,
    array $queue_config,
  ) {
    $this->queueConfig = $queue_config + [
      'suspendMaximumWait' => 30.0,
    ];
    $this->queueFactory = $queueFactory;
    $this->queueManager = $queueManager;
    $this->logger = $logger;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public function run() {
    // Allow execution to continue even if the request gets cancelled.
    @ignore_user_abort(TRUE);

    // Force the current user to anonymous to ensure consistent permissions on
    // cron runs.
    $this->accountSwitcher->switchTo(new AnonymousUserSession());

    // Try to allocate enough time to run all the hook_cron implementations.
    Environment::setTimeLimit(240);

    $return = FALSE;

    // Try to acquire cron lock.
    if (!$this->lock->acquire('cron', 900.0)) {
      // Cron is still running normally.
      $this->logger->warning('Attempting to re-run cron while it is already running.');
    }
    else {
      $this->invokeCronHandlers();

      // Process cron queues.
      $this->processQueues();

      $this->setCronLastTime();

      // Release cron lock.
      $this->lock->release('cron');

      // Add watchdog message.
      $this->logger->info('Cron run completed.');

      // Return TRUE so other functions can check if it did run successfully
      $return = TRUE;
    }

    // Restore the user.
    $this->accountSwitcher->switchBack();

    return $return;
  }

  /**
   * Records and logs the request time for this cron invocation.
   */
  protected function setCronLastTime() {
    // Record cron time.
    $request_time = $this->time->getRequestTime();
    $this->state->set('system.cron_last', $request_time);
  }

  /**
   * Invokes any cron handlers implementing hook_cron.
   */
  protected function invokeCronHandlers() {
    $module_previous = '';

    // If detailed logging isn't enabled, don't log individual execution times.
    $time_logging_enabled = \Drupal::config('system.cron')->get('logging');
    $logger = $time_logging_enabled ? $this->logger : new NullLogger();

    // Iterate through the modules calling their cron handlers (if any):
    $this->moduleHandler->invokeAllWith('cron', function (callable $hook, string $module) use (&$module_previous, $logger) {
      if (!$module_previous) {
        $logger->info('Starting execution of @module_cron().', [
          '@module' => $module,
        ]);
      }
      else {
        $logger->info('Starting execution of @module_cron(), execution of @module_previous_cron() took @time.', [
          '@module' => $module,
          '@module_previous' => $module_previous,
          '@time' => Timer::read('cron_' . $module_previous) . 'ms',
        ]);
      }
      Timer::start('cron_' . $module);

      // Do not let an exception thrown by one module disturb another.
      try {
        $hook();
      }
      catch (\Exception $e) {
        Error::logException($this->logger, $e);
      }

      Timer::stop('cron_' . $module);
      $module_previous = $module;
    });
    if ($module_previous) {
      $logger->info('Execution of @module_previous_cron() took @time.', [
        '@module_previous' => $module_previous,
        '@time' => Timer::read('cron_' . $module_previous) . 'ms',
      ]);
    }
  }

}
