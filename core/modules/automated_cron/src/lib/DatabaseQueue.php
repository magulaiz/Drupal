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
    $cache = &drupal_static('__automated_cron_instant_queue__', []);
    $id = parent::createItem($data);
    $cache[$this->name] = ($cache[$this->name] ?? 0) + 1;
    return $id;
  }

}
