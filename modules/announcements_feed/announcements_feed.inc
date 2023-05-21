<?php

/**
 * @file
 * Announcements Feed helper functions.
 */

/**
 * Page callback for the announcement entry.
 */
function announcements_feed_get_announcements() {
  drupal_add_css(drupal_get_path('module', 'announcements_feed')
    . '/announcements_feed.css', array(
      'group' => CSS_DEFAULT,
      'every_page' => TRUE,
    ));
  try {
    $announcements = announcements_feed_get_all_announcements();
  }
  catch (Exception $e) {
    return theme('status_message', drupal_set_message($e->getMessage(), 'error'));
  }
  $build = array();
  foreach ($announcements as $announcement) {
    $key = $announcement['featured'] ? '#featured' : '#standard';
    $build[$key][] = $announcement;
  }
  $build = array_merge($build, array(
    '#theme' => 'announcements_feed',
    '#count' => count($announcements),
    '#feed_link' => variable_get('announcements_feed_link'),
  ));

  return $build;
}

/**
 * Returns the list of Announcements.
 *
 * @return array
 *   A build array with announcements.
 */
function announcements_feed_get_all_announcements() {
  $announcements = announcements_feed_fetch();
  $announcements_feed = array();
  foreach ($announcements as $announcement) {
    $announcements_feed[] = array(
      'id' => $announcement['id'],
      'title' => $announcement['title'],
      'link' => $announcement['url'],
      'date_modified' => $announcement['date_modified'],
      'date_published' => $announcement['date_published'],
      'teaser' => $announcement['content_html'],
      'version' => $announcement['_drupalorg']['version'],
      'featured' => (bool) $announcement['_drupalorg']['featured'],
    );
  }

  return $announcements_feed;
}

/**
 * Fetches the feed either from a local cache or fresh remotely.
 *
 * The feed follows the "JSON Feed" format:
 * - https://www.jsonfeed.org/version/1.1/
 *
 * The structure of an announcement item in the feed is:
 *   - id: Id.
 *   - title: Title of the announcement.
 *   - content_html: Announcement teaser.
 *   - url: URL
 *   - date_modified: Last updated timestamp.
 *   - date_published: Created timestamp.
 *   - _drupalorg.featured: 1 if featured, 0 if not featured.
 *   - _drupalorg.version: Target version of Drupal, as a Composer version.
 *
 * @return array
 *   An array of announcements from the feed relevant to the Drupal version.
 *   The array is empty if there were no matching announcements. If an error
 *   occurred while fetching/decoding the feed, it is thrown as an exception.
 *
 * @throws Exception
 */
function announcements_feed_fetch() {
  $announcements = cache_get('announcements_feed', 'cache_announcements_feed');
  if (empty($announcements)) {
    $announcements_feed_url = variable_get('announcements_feed_url');
    $response = drupal_http_request($announcements_feed_url);
    if ($response->code == 200) {
      $feeds = json_decode($response->data, TRUE);
      if (is_null($feeds)) {
        watchdog('error', 'JSON data is not parsable please check the api.', WATCHDOG_ERROR);
        throw new Exception('Announcement Feed unable to parse the JSON data. Check logs for more.');
      }
      $announcements = array();
      if ($feeds['items']) {
        $announcements = $feeds['items'];
      }
      $announcements = array_filter($announcements, 'announcements_feed_filter_announcements');
      cache_set('announcements_feed', $announcements, 'cache_announcements_feed', REQUEST_TIME + variable_get('announcements_feed_max_age'));
    }
    else {
      watchdog('error', $response->code, (array) $response, WATCHDOG_ERROR);
      throw new Exception('Announcements Feed Url fetch failed with an error: @code. Check logs for more',
        array('@code' => $response->code));
    }
  }
  else {
    $announcements = $announcements->data;

  }
  // The drupal.org endpoint is sorted by created date in descending order.
  // We will limit the announcements based on the configuration limit.
  $announcements_feed_limit = variable_get('announcements_feed_limit', 10);
  if (empty($announcements_feed_limit)) {
    $announcements_feed_limit = 10;
  }
  $announcements = array_slice($announcements, 0, $announcements_feed_limit);
  // For the remaining announcements, put all the featured announcements
  // before the rest.
  uasort($announcements, 'announcements_feed_sort_featured');

  return $announcements;
}

/**
 * Sort the elements of announcements_feed by values in comparison function.
 */
function announcements_feed_sort_featured($a, $b) {
  $a_value = (int) $a['_drupalorg']['featured'];
  $b_value = (int) $b['_drupalorg']['featured'];
  if ($a_value == $b_value) {
    return 0;
  }

  return ($a_value < $b_value) ? -1 : 1;
}

/**
 * Filter the validated and relavant announcements only.
 *
 * @param array $announcement
 *   Announcement feed array to filter the announcements.
 *
 * @return bool
 *   Return True if $announcement is relavant and the url is validated.
 */
function announcements_feed_filter_announcements($announcement) {
  $announcement_url = '';
  $announcement_version = '';
  if (!empty($announcement['url'])) {
    $announcement_url = $announcement['url'];
  }
  if (!empty($announcement['_drupalorg']['version'])) {
    $announcement_version = $announcement['_drupalorg']['version'];
  }

  return announcements_feed_validate_url($announcement_url) && announcements_feed_is_relevant_item($announcement_version);
}

/**
 * Check whether the version given is relevant to the Drupal version used.
 *
 * @param string $version
 *   Version to check.
 *
 * @return bool
 *   Return True if the version matches Drupal version.
 */
function announcements_feed_is_relevant_item($version) {
  if ($version == '*') {
    return TRUE;
  }
  // Split the version if received in || formats.
  $version_patterns = '/\|\|/';
  $all_versions = preg_split($version_patterns, $version);
  // supports. Also, operation is optional and defaults to equals.
  $p_op = '(?P<operation>!=|\^|==|=|<|<=|>|>=|<>)?';
  $operations = '=';
  // Extracts major version from version string like 7, 8, 9.
  $p_major = '(?P<major>\d+)';
  // Extracts minor version from version string.
  $p_minor = '(?P<minor>(?:\d+|x)(?:-[A-Za-z]+\d+)?)';
  foreach ($all_versions as $version) {
    if (preg_match("/^\s*$p_op\s*$p_major(\.$p_minor)?/", $version, $matches)) {
      $feed_version = $matches['major'];
      if (!empty($matches['minor'])) {
        $feed_version = $matches['major'] . '.' . $matches['minor'];
      }
      if (!empty($matches['operation'])) {
        $operations = $matches['operation'];
        if ($operations == '^') {
          $operations = '>=';
        }
      }
      if ((isset($operations) && version_compare(VERSION, $feed_version, $operations))) {
        return TRUE;
      }
    }
  }

  return FALSE;
}

/**
 * Check whether a link is controlled by D.O.
 *
 * @param string $url
 *   URL to check.
 *
 * @return bool
 *   Return True if the URL is controlled by the D.O.
 */
function announcements_feed_validate_url($url) {
  if (empty($url)) {
    return FALSE;
  }
  $host = parse_url($url, PHP_URL_HOST);

  // First character can only be a letter or a digit.
  // @see https://www.rfc-editor.org/rfc/rfc1123#page-13
  return $host && preg_match('/^([a-zA-Z0-9][a-zA-Z0-9\-_]*\.)?drupal\.org$/', $host);
}
