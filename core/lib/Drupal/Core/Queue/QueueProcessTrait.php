<?php

namespace Drupal\Core\Queue;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Utility\Error;
use Psr\Log\LoggerInterface;

/**
 * Provides reused functions for Queue processing.
 *
 * @internal
 */
trait QueueProcessTrait {

  /**
   * The queue service.
   *
   * @var Drupal\Core\Queue\QueueFactory
   */
  protected QueueFactory $queueFactory;

  /**
   * The queue plugin manager.
   *
   * @var Drupal\Core\Queue\QueueWorkerManagerInterface
   */
  protected QueueWorkerManagerInterface $queueManager;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * The time service.
   *
   * @var Drupal\Component\Datetime\TimeInterface
   */
  protected TimeInterface $time;

  /**
   * The queue config.
   *
   * @var array
   */
  protected array $queueConfig;

  /**
   * Processes cron queues.
   *
   * @param array $queues_to_process
   *   Queue and the number of items in the queue to be processed.
   */
  protected function processQueues(array $queues_to_process = []): void {
    $max_wait = (float) $this->queueConfig['suspendMaximumWait'];

    // Build a stack of queues to work on.
    /** @var array<array{process_from: int<0, max>, queue: \Drupal\Core\Queue\QueueInterface, worker: \Drupal\Core\Queue\QueueWorkerInterface}> $queues */
    $queues = [];
    foreach ($this->queueManager->getDefinitions() as $queue_name => $queue_info) {
      if (!isset($queue_info['cron']) || (!empty($queues_to_process) && !isset($queues_to_process[$queue_name]))) {
        continue;
      }
      $queue = $this->queueFactory->get($queue_name);
      // Make sure every queue exists. There is no harm in trying to recreate
      // an existing queue.
      $queue->createQueue();
      $worker = $this->queueManager->createInstance($queue_name);
      $queues[] = [
        // Set process_from to zero so each queue is always processed
        // immediately for the first time. This process_from timestamp will
        // change if a queue throws a delayable SuspendQueueException.
        'process_from' => 0,
        'queue' => $queue,
        'worker' => $worker,
        'max_items' => $queues_to_process[$queue_name] ?? 0,
      ];
    }

    // Work through stack of queues, re-adding to the stack when a delay is
    // necessary.
    while ($item = array_shift($queues)) {
      [
        'queue' => $queue,
        'worker' => $worker,
        'process_from' => $process_from,
        'max_items' => $max_items,
      ] = $item;

      // Each queue will be processed immediately when it is reached for the
      // first time, as zero > currentTime will never be true.
      if ($process_from > $this->time->getCurrentMicroTime()) {
        $this->usleep((int) round($process_from - $this->time->getCurrentMicroTime(), 3) * 1000000);
      }

      try {
        $this->processQueue($queue, $worker, $max_items);
      }
      catch (SuspendQueueException $e) {
        // Return to this queue after processing other queues if the delay is
        // within the threshold.
        if ($e->isDelayable() && ($e->getDelay() < $max_wait)) {
          $item['process_from'] = $this->time->getCurrentMicroTime() + $e->getDelay();
          // Place this queue back in the stack for processing later.
          array_push($queues, $item);
        }
      }

      // Reorder the queue by next 'process_from' timestamp.
      usort($queues, function (array $queueA, array $queueB) {
        return $queueA['process_from'] <=> $queueB['process_from'];
      });
    }
  }

  /**
   * Processes a cron queue.
   *
   * @param \Drupal\Core\Queue\QueueInterface $queue
   *   The queue.
   * @param \Drupal\Core\Queue\QueueWorkerInterface $worker
   *   The queue worker.
   * @param int $max_items
   *   Maximum number of items to be processed.
   *
   * @throws \Drupal\Core\Queue\SuspendQueueException
   *   If the queue was suspended.
   */
  protected function processQueue(QueueInterface $queue, QueueWorkerInterface $worker, int $max_items = 0): void {
    $lease_time = $worker->getPluginDefinition()['cron']['time'];
    $end = $this->time->getCurrentTime() + $lease_time;
    $no_items = 0;
    while (($max_items === 0 || $no_items++ < $max_items) && $this->time->getCurrentTime() < $end && ($item = $queue->claimItem($lease_time))) {
      try {
        $worker->processItem($item->data);
        $queue->deleteItem($item);
      }
      catch (DelayedRequeueException $e) {
        // The worker requested the task not be immediately re-queued.
        // - If the queue doesn't support ::delayItem(), we should leave the
        // item's current expiry time alone.
        // - If the queue does support ::delayItem(), we should allow the
        // queue to update the item's expiry using the requested delay.
        if ($queue instanceof DelayableQueueInterface) {
          // This queue can handle a custom delay; use the duration provided
          // by the exception.
          $queue->delayItem($item, $e->getDelay());
        }
      }
      catch (RequeueException) {
        // The worker requested the task be immediately requeued.
        $queue->releaseItem($item);
      }
      catch (SuspendQueueException $e) {
        // If the worker indicates the whole queue should be skipped, release
        // the item and go to the next queue.
        $queue->releaseItem($item);

        $this->logger->debug('A worker for @queue queue suspended further processing of the queue.', [
          '@queue' => $worker->getPluginId(),
        ]);

        // Skip to the next queue.
        throw $e;
      }
      catch (\Exception $e) {
        // In case of any other kind of exception, log it and leave the item
        // in the queue to be processed again later.
        Error::logException($this->logger, $e);
      }
    }
  }

  /**
   * Delay execution in microseconds.
   *
   * @param int $microseconds
   *   Halt time in microseconds.
   */
  protected function usleep(int $microseconds): void {
    usleep($microseconds);
  }

}
