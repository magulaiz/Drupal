<?php

namespace Drupal\mongodb\Queue;

use Drupal\Core\Queue\QueueDatabaseFactory as CoreQueueDatabaseFactory;

/**
 * The MongoDB implementation of \Drupal\Core\Queue\QueueDatabaseFactory.
 */
class QueueDatabaseFactory extends CoreQueueDatabaseFactory {

  /**
   * {@inheritdoc}
   */
  public function get($name) {
    // MongoDB needs it own version of the DatabaseQueue.
    return new DatabaseQueue($name, $this->connection);
  }

}
