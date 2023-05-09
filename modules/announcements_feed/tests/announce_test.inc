<?php

/**
 * @file
 * Contains helper function for the tests.
 */

/**
 * Function to set feed_url with respect to endpoint also reset the cache.
 */
function set_announce_test_endpoint(string $test_endpoint) {
  variable_set('feed_json_url', $test_endpoint);
  // Clear the announcement cache if available.
  if (!_cache_get_object('cache_announce')->isEmpty()) {
    cache_clear_all('announce', 'cache_announce');
  }

}
