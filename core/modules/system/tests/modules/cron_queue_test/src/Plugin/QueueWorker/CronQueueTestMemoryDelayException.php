<?php

namespace Drupal\cron_queue_test\Plugin\QueueWorker;

use Drupal\Core\Queue\DelayedRequeueException;
use Drupal\Core\Queue\QueueWorkerBase;

/**
 * A queue worker for testing cron exception handling.
 *
 * @QueueWorker(
 *   id = "cron_queue_test_memory_delay_exception",
 *   title = @Translation("Memory delay exception test"),
 *   cron = {
 *     "time" = 1,
 *     "lease_time" = 2
 *   }
 * )
 */
class CronQueueTestMemoryDelayException extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Set the delay to something larger than the original lease.
    $lease_time = $this->pluginDefinition['cron']['lease_time'];
    throw new DelayedRequeueException($lease_time + 100);
  }

}
