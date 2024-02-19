<?php

namespace Drupal\mongodb\modules\statistics;

use Drupal\statistics\NodeStatisticsDatabaseStorage as CoreNodeStatisticsDatabaseStorage;
use MongoDB\BSON\UTCDateTime;

// cspell:ignore daycount totalcount

/**
 * The MongoDB implementation of \Drupal\statistics\NodeStatisticsDatabaseStorage.
 */
class NodeStatisticsDatabaseStorage extends CoreNodeStatisticsDatabaseStorage {

  /**
   * {@inheritdoc}
   */
  public function recordView($id) {
    try {
      $this->connection->insert('node_counter')
        ->fields([
          'daycount' => 1,
          'totalcount' => 1,
          'timestamp' => $this->getRequestTime(),
          'nid' => $id,
        ])
        ->execute();
      return TRUE;
    }
    catch (\Exception $e) {
      $prefixed_table = $this->connection->getMongodbPrefixedTable('node_counter');
      $this->connection->getConnection()->{$prefixed_table}->updateOne(
        [
          'nid' => $id,
        ],
        [
          '$inc' => [
            'daycount' => 1,
            'totalcount' => 1,
          ],
          '$set' => [
            'timestamp' => new UTCDateTime($this->getRequestTime() * 1000),
          ],
        ],
      );
      return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fetchViews($ids) {
    foreach ($ids as &$id) {
      $id = (int) $id;
    }

    return parent::fetchViews($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteViews($id) {
    return parent::deleteViews((int) $id);
  }

}
