<?php

declare(strict_types=1);

namespace Drupal\automated_cron\lib;

use Drupal\Core\Queue\QueueDatabaseFactory as BaseQueueDatabaseFactory;

/**
 * {@inheritdoc}
 */
class QueueDatabaseFactory extends BaseQueueDatabaseFactory {

  /**
   * {@inheritdoc}
   */
  public function get($name) {
    return new DatabaseQueue($name, $this->connection);
  }

}
