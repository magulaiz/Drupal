<?php

/**
 * @file
 * Post update functions for Aggregator.
 */

/**
 * Delete the aggregator_feeds queue to eliminate old items containing entities.
 */
function aggregator_post_update_delete_queue_items(&$sandbox = NULL) {
  \Drupal::queue('aggregator_feeds')->deleteQueue();
}
