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
  $new_announcements = announcements_feed_get_new_announcements();
  foreach ($announcements as $key => $announcement) {
    // Add an attribute 'new' to the announcements to identify new items.
    $announcements[$key]['new'] = in_array($announcement['id'],
      $new_announcements) ? $announcement['id'] : '';
  }
  if (!empty($new_announcements)) {
    // Store the ids of announcements for the user to mark it as read items.
    announcements_feed_set_announcement_status(announcements_feed_fetch_ids());
  }
  foreach ($announcements as $announcement) {
    $announcements_feed[] = [
      'id' => $announcement['id'],
      'title' => $announcement['title'],
      'link' => $announcement['url'],
      'date_modified' => format_date(strtotime($announcement['date_modified']), 'custom', 'm/d/Y - H:i:s'),
      'date_published' => format_date(strtotime($announcement['date_published']), 'custom', 'm/d/Y - H:i:s'),
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
      $announcements = $feeds['items'] ?? [];
      $announcements = array_filter($announcements, function (array $announcement) {
        return announcements_feed_validate_url($announcement['url'] ?? '') && announcements_feed_is_relevant_item($announcement['_drupalorg']['version'] ?? '');
      });
      cache_set('announcements_feed', $announcements, 'cache_announcements_feed', REQUEST_TIME + variable_get('max_age'));
    }
    else {
      watchdog('error', $response->code, (array) $response, WATCHDOG_ERROR);
      throw new Exception('Announcements Feed Url fetch failed with an error: @code @error. Check logs for more',
        array('@code' => $response->code, '@error' => $response->error));
    }

  }
  else {
    $announcements = $announcements->data;
    $announcements = array_filter($announcements, 'announcements_feed_is_relevant_item');

  }
  return $announcements;
}

/**
 * Fetch new announcements for the logged-in user.
 *
 * Return an array of ids of new announcements for the user.
 *
 * @return array
 *   Ids of new announcements for the user, empty array if there are no unread
 *   announcements for the user.
 */
function announcements_feed_get_new_announcements() {
  // Get the announcements viewed by the user.
  $user_announcements = announcements_feed_get_announcement_status();
  // Fetch the announcements from the feed.
  $announcements = announcements_feed_fetch_ids();
  return array_diff($announcements, $user_announcements);
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
  /*
  if (Semver::satisfies(VERSION, $version)) {

  }
  if ($announcement['version'] >= '^7' || VERSION == $announcement['version']) {
  return TRUE;
  }
   */
  if (preg_match('/(>|>=)7/', $version)) {
    return TRUE;
  }
  return FALSE;
}

/**
 * Get all announcements read by the current user.
 *
 * @return array
 *   All announcement ids stored against current user's id, empty if no
 *   announcement data stored for the user.
 */
function announcements_feed_get_announcement_status() {
  global $user;
  if ($user->data && array_key_exists('announcements_feed', $user->data)) {
    return $user->data['announcements_feed'];
  }
  else {
    return [];
  }
}

/**
 * Set the read status of announcements for the current user.
 *
 * @parm array $announcements
 *  IDs of announcements to store against the current user.
 */
function announcements_feed_set_announcement_status(array $announcements) {
  global $user;
  $user->data['announcements_feed'] = $announcements;
  user_save($user);
}

/**
 * Fetch ids of announcements.
 *
 * @return array
 *   An array with ids of all announcements in the feed.
 */
function announcements_feed_fetch_ids() {
  $announcements = announcements_feed_fetch();
  return array_column($announcements, 'id');
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
