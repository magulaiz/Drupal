<?php

namespace Drupal\Core\Queue;

/**
 * QueueFactoryInterface is the common interface for all Queue factories.
 */
interface QueueFactoryInterface {

  /**
   * Returns a queue instance.
   *
   * @param string $name
   *   The name of the queue.
   *
   * @return \Drupal\Core\Queue\QueueInterface
   *   The queue instance.
   */
  public function get(string $name): QueueInterface;

}
