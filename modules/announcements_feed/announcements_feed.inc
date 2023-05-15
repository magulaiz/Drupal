<?php

/**
 * @file
 * Require_once DRUPAL_ROOT . '/vendor/autoload.php';.
 */

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
  $build += [
    '#theme' => 'announcements_feed',
    '#count' => count($announcements),
    '#feed_link' => variable_get('feed_link'),
  ];
  return $build;
}

/**
 * Get announcements for the current logged-in user.
 *
 * Return an array of announcements with an additional attribute new for the
 * new items in the list.
 *
 * @return array
 *   Announcements for the current logged-in user.
 */
function announcements_feed_get_all_announcements() {
  $announcements = announcements_feed_fetch();
  $announcements_feed = array();
  foreach ($announcements as $announcement) {
    $announcements_feed[] = [
      'id' => $announcement['id'],
      'title' => $announcement['title'],
      'link' => $announcement['url'],
      'date_modified' => $announcement['date_modified'],
      'date_published' => $announcement['date_published'],
      'teaser' => $announcement['content_html'],
      'version' => $announcement['_drupalorg']['version'],
      'featured' => (bool) $announcement['_drupalorg']['featured'],
    ];
  }
  return $announcements_feed;
}

/**
 * Helper function that fetch the feed.
 *
 * Return all announcements from the feed relevant to the Drupal version.
 * Structure of the JSON feed:
 *   id: Id.
 *   title: Title of the announcement.
 *   teaser: Announcement teaser.
 *   link: URL
 *   sticky: 1 if featured, 0 if not featured.
 *   version: Target version of Drupal in SemVer format.
 *   updated: Last updated timestamp.
 *
 * @return array
 *   An array of Announcements from the feed, or an empty array if the feed
 *   is empty or an error in fetching/decoding feed.
 *
 * @throws Exception
 */
function announcements_feed_fetch() {
  $announcements = cache_get('announce', 'cache_announce');
  if (empty($announcements)) {
    $feed_url = variable_get('feed_url');
    $response = drupal_http_request($feed_url);
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
      $announcements = array_filter($announcements, function (array $announcement) {
        $announcement_url = '';
        $announcement_version = '';
        if (!empty($announcement['url'])) {
          $announcement_url = $announcement['url'];
        }
        if (!empty($announcement['_drupalorg']['version'])) {
          $announcement_version = $announcement['_drupalorg']['version'];
        }
        return announcements_feed_validate_url($announcement_url) && announcements_feed_is_relevant_item($announcement_version);
      });
      cache_set('announcements_feed', $announcements, 'cache_announce', REQUEST_TIME + variable_get('max_age'));
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
  $announce_limit = !empty(variable_get('announce_limit')) ? variable_get('announce_limit') : 10;
  $announcements = array_slice($announcements, 0, $announce_limit);
  // For the remaining announcements, put all the featured announcements
  // before the rest.
  uasort($announcements, function ($a, $b) {
    $a_value = (int) $a['_drupalorg']['featured'];
    $b_value = (int) $b['_drupalorg']['featured'];
    if ($a_value == $b_value) {
      return 0;
    }
    return ($a_value < $b_value) ? -1 : 1;
  });
  return $announcements;
}

/**
 * Check whether the feed item is relevant to the Drupal version used.
 *
 * @param array $version
 *   Announcement feed item to check.
 *
 * @return bool
 *   Return True if $announcement['version'] matches Drupal version.
 */
function announcements_feed_is_relevant_item(string $version): bool {
  // Return true if version is one of supporting Drupal 7.
  if (preg_match('/^(\^7\s*\|\|\s*)|\|\|\s*\^7(\s*\|\|)?|\^7\s*|=7|\*|7\.[0-9]{2}/', $version)) {
    return TRUE;
  }
  return FALSE;
}

/**
 * Check whether the link of announcement controlled by D.O.
 *
 * @param array $url
 *   Announcement feed item to check.
 *
 * @return bool
 *   Return True if $announcement['link'] is controlled by the D.O.
 */
function announcements_feed_validate_url(string $url): bool {
  if (empty($url)) {
    return FALSE;
  }
  $host = parse_url($url, PHP_URL_HOST);

  // First character can only be a letter or a digit.
  // @see https://www.rfc-editor.org/rfc/rfc1123#page-13
  return $host && preg_match('/^([a-zA-Z0-9][a-zA-Z0-9\-_]*\.)?drupal\.org$/', $host);
}
