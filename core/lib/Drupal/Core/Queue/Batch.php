<?php

namespace Drupal\Core\Queue;

/**
 * Defines a batch queue handler used by the Batch API.
 *
 * This implementation:
 * - Ensures FIFO ordering.
 * - Allows an item to be repeatedly claimed until it is actually deleted (no
 *   notion of lease time or 'expire' date), to allow multipass operations.
 *
 * Stale items from failed batches are cleaned from the {queue} table on cron
 * using the 'created' date.
 *
 * @ingroup queue
 */
class Batch extends DatabaseQueue implements BatchQueueInterface {

  /**
   * {@inheritdoc}
   *
   * Unlike \Drupal\Core\Queue\DatabaseQueue::claimItem(), this method provides
   * a default lease time of 0 (no expiration) instead of 30. This allows the
   * item to be claimed repeatedly until it is deleted.
   */
  public function claimItem($lease_time = 0) {
    try {
      $item = $this->connection->queryRange('SELECT [data], [item_id] FROM {' . static::TABLE_NAME . '} q WHERE [name] = :name ORDER BY [item_id] ASC', 0, 1, [':name' => $this->name])->fetchObject();
      if ($item) {
        $item->data = unserialize($item->data);
        return $item;
      }
    }
    catch (\Exception $e) {
      $this->catchException($e);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllItems() {
    $result = [];
    try {
      $items = $this->connection->query('SELECT [data] FROM {' . static::TABLE_NAME . '} q WHERE [name] = :name ORDER BY [item_id] ASC', [':name' => $this->name])->fetchAll();
      foreach ($items as $item) {
        $result[] = unserialize($item->data);
      }
    }
    catch (\Exception $e) {
      $this->catchException($e);
    }
    return $result;
  }

}
