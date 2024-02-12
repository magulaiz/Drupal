<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\aggregator\FeedStorageInterface;
use MongoDB\BSON\UTCDateTime;

/**
 * The MongoDB implementation of \Drupal\aggregator\FeedStorage.
 */
class FeedStorage extends ContentEntityStorage implements FeedStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function getFeedIdsToRefresh() {
    $request_time = new UTCDateTime(REQUEST_TIME * 1000);
    $query = $this->database->select('aggregator_feed', 'a')
      ->fields('a', ['fid']);
    $query->addMultiplyField('refresh_milliseconds', 'refresh', 1000);
    $query->addSumField('checked_plus_refresh_milliseconds', ['checked', 'refresh_milliseconds']);
    return $query
      ->condition('checked_plus_refresh_milliseconds', $request_time, '<')
      ->condition('refresh', AGGREGATOR_CLEAR_NEVER, '<>')
      ->condition('queued', new UTCDateTime(0))
      ->execute()
      ->fetchCol();
  }

}
