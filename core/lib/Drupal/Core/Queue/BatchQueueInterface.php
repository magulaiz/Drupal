<?php

namespace Drupal\Core\Queue;

/**
 * Batch API queue which is not part of QueueInterface.
 */
interface BatchQueueInterface extends QueueInterface {

  /**
   * Retrieves all remaining items in the queue.
   *
   * @return array
   *   An array of queue items.
   */
  public function getAllItems();

}
