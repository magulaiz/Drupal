<?php

namespace Drupal\mongodb\Queue;

use Drupal\Core\Queue\QueueFactory as CoreQueueFactory;

/**
 * Defines the queue factory.
 */
class QueueFactory extends CoreQueueFactory {

  /**
   * {@inheritdoc}
   */
  public function get($name, $reliable = FALSE) {
    if (!isset($this->queues[$name])) {
      // If it is a reliable queue, check the specific settings first.
      if ($reliable) {
        $service_name = $this->settings->get('queue_reliable_service_' . $name);
      }
      // If no reliable queue was defined, check the service and global
      // settings, fall back to queue.database.
      if (empty($service_name)) {
        $service_name = $this->settings->get('queue_service_' . $name, $this->settings->get('queue_default', 'mongodb.queue.database'));
      }
      $this->queues[$name] = $this->container->get($service_name)->get($name);
    }
    return $this->queues[$name];
  }

}
