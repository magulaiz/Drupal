<?php

/**
 * @file
 * Contains helper function for the tests.
 */

/**
 * Function to set feed_url with respect to endpoint also reset the cache.
 */
function set_announce_test_endpoint(string $test_endpoint) {
  variable_set('feed_url', $test_endpoint);
}
