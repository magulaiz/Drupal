<?php

namespace Drupal\mongodb\Queue;

use Drupal\Core\Queue\DatabaseQueue as CoreDatabaseQueue;

/**
 * The MongoDB implementation of \Drupal\Core\Queue\DatabaseQueue.
 */
class DatabaseQueue extends CoreDatabaseQueue {

  /**
   * Indicator for the existence of the database table.
   *
   * @var bool
   */
  protected $tableExists = FALSE;

  /**
   * {@inheritdoc}
   */
  public function createItem($data) {
    // For MongoDB the table needs to exist. Otherwise MongoDB creates one
    // without the correct validation.
    if (!$this->tableExists) {
      $this->tableExists = $this->ensureTableExists();
    }

    return $this->doCreateItem($data);
  }

  /**
   * {@inheritdoc}
   */
  public function numberOfItems() {
    try {
      $prefixed_table = $this->connection->getMongodbPrefixedTable(static::TABLE_NAME);
      return $this->connection->getConnection()->{$prefixed_table}->count(
        ['name' => ['$eq' => $this->name]],
        []
      );
    }
    catch (\Exception $e) {
      $this->catchException($e);
      // If there is no table there cannot be any items.
      return 0;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function claimItem($lease_time = 30) {
    // Claim an item by updating its expire fields. If claim is not successful
    // another thread may have claimed the item in the meantime. Therefore loop
    // until an item is successfully claimed or we are reasonably sure there
    // are no unclaimed items left.
    while (TRUE) {
      try {
        $item = $this->connection->select(static::TABLE_NAME, 'b')
          ->fields('b', ['data', 'created', 'item_id'])
          ->condition('expire', 0)
          ->condition('name', $this->name)
          ->orderBy('created')
          ->orderBy('item_id')
          ->range(0, 1)
          ->execute()
          ->fetchObject();
      }
      catch (\Exception $e) {
        $this->catchException($e);
        // If the table does not exist there are no items currently available to
        // claim.
        return FALSE;
      }
      if ($item) {
        // Try to update the item. Only one thread can succeed in UPDATEing the
        // same row. We cannot rely on REQUEST_TIME because items might be
        // claimed by a single consumer which runs longer than 1 second. If we
        // continue to use REQUEST_TIME instead of the current time(), we steal
        // time from the lease, and will tend to reset items before the lease
        // should really expire.
        $update = $this->connection->update(static::TABLE_NAME)
          ->fields([
            'expire' => \Drupal::time()->getCurrentTime() + $lease_time,
          ])
          // For MongoDB the item_id must be an integer.
          ->condition('item_id', (int) $item->item_id)
          ->condition('expire', 0);
        // If there are affected rows, this update succeeded.
        if ($update->execute()) {
          $item->data = unserialize($item->data);
          return $item;
        }
      }
      else {
        // No items currently available to claim.
        return FALSE;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function releaseItem($item) {
    try {
      $update = $this->connection->update(static::TABLE_NAME)
        ->fields([
          'expire' => 0,
        ])
        // For MongoDB the item_id must be an integer.
        ->condition('item_id', (int) $item->item_id);
      return (bool) $update->execute();
    }
    catch (\Exception $e) {
      $this->catchException($e);
      // If the table doesn't exist we should consider the item released.
      return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function delayItem($item, int $delay) {
    // Only allow a positive delay interval.
    if ($delay < 0) {
      throw new \InvalidArgumentException('$delay must be non-negative');
    }

    try {
      // Add the delay relative to the current time.
      $expire = \Drupal::time()->getCurrentTime() + $delay;
      // Update the expiry time of this item.
      $update = $this->connection->update(static::TABLE_NAME)
        ->fields([
          'expire' => $expire,
        ])
        ->condition('item_id', (int) $item->item_id);
      return (bool) $update->execute();
    }
    catch (\Exception $e) {
      $this->catchException($e);
      // If the table doesn't exist we should consider the item nonexistent.
      return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteItem($item) {
    try {
      $this->connection->delete(static::TABLE_NAME)
        // For MongoDB the item_id must be an integer.
        ->condition('item_id', (int) $item->item_id)
        ->execute();
    }
    catch (\Exception $e) {
      $this->catchException($e);
    }
  }

}
