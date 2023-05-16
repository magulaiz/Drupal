<?php

/**
 * @file
 * Contains helper function for the tests.
 */

/**
 * Function to set feed url with respect to endpoint also reset the cache.
 */
function set_announce_feed_test_endpoint(string $announcements_feed_url) {
  variable_set('announcements_feed_url', $announcements_feed_url);
}
