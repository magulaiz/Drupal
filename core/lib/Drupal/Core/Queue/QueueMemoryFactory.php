<?php

namespace Drupal\Core\Queue;

/**
 * Defines the key/value store factory for the memory backend.
 */
class QueueMemoryFactory implements QueueFactoryInterface {

  /**
   * Constructs a new queue object for a given name.
   *
   * @param string $name
   *   The name of the collection holding key and value pairs.
   *
   * @return \Drupal\Core\Queue\Memory
   *   A key/value store implementation for the given $collection.
   */
  public function get(string $name): Memory {
    return new Memory($name);
  }

}
