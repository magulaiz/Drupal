<?php

declare(strict_types=1);

namespace Drupal\automated_cron\lib;

use Drupal\Core\Queue\DatabaseQueue as BaseDatabaseQueue;

/**
 * {@inheritdoc}
 */
class DatabaseQueue extends BaseDatabaseQueue {

  /**
   * {@inheritdoc}
   */
  public function createItem($data) {
    // Track that the items were added to the queue.
    // Only if items were added to the queue we will run the instant
    // queue processing. This cache will be read in
    // Drupal\automated_cron\EventSubscriber\AutomatedCron::onTerminate
    // and trigger the queue processing.
    $id = parent::createItem($data);
    \Drupal::service('automated_cron.instant_queue')->addToInstantQueue($this->name);
    return $id;
  }

}
