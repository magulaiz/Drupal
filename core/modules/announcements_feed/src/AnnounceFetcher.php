<?php

namespace Drupal\announcements_feed;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueExpirableFactory;
use GuzzleHttp\ClientInterface;
use Composer\Semver\Semver;
use Psr\Log\LoggerInterface;
use Drupal\Component\Serialization\Json;

/**
 * Service to fetch announcements from the external feed.
 */
class AnnounceFetcher {

  /**
   * The Http Client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The ConfigFactory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The tempstore service.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueExpirableFactory
   */
  protected $tempStore;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * URL for the announcement.
   *
   * @var string
   */
  protected $feedUrl;

  /**
   * Construct an AnnounceFetcher service.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The http client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory service.
   * @param \Drupal\Core\KeyValueStore\KeyValueExpirableFactory $temp_store
   *   The tempstore factory service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   * @param string $feed_url
   *   The feed url path.
   */
  public function __construct(ClientInterface $http_client, ConfigFactoryInterface $config, KeyValueExpirableFactory $temp_store, LoggerInterface $logger, string $feed_url) {
    $this->httpClient = $http_client;
    $this->config = $config->get('announcements_feed.settings');
    $this->tempStore = $temp_store->get('announcements_feed');
    $this->logger = $logger;
    $this->feedUrl = $feed_url;
  }

  /**
   * Fetch ids of announcements.
   *
   * @return array
   *   An array with ids of all announcements in the feed.
   */
  public function fetchIds(): array {
    $announcements = $this->fetch();
    $ids = array_column($announcements, 'id');
    return $ids;
  }

  /**
   * Check whether the feed item is relevant to the Drupal version used.
   *
   * @param array $announcement
   *   Announcement feed item to check.
   *
   * @return bool
   *   Return True if $announcement['version'] matches Drupal version.
   */
  protected function isRelevantItem(array $announcement): bool {
    try {
      $relevant_content = Semver::satisfies(\Drupal::VERSION, $announcement['version']);
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      $relevant_content = FALSE;
    }
    return $relevant_content;
  }

  /**
   * Check whether the link of announcement controlled by D.O.
   *
   * @param array $announcement
   *   Announcement feed item to check.
   *
   * @return bool
   *   Return True if $announcement['link'] is controlled by the D.O.
   */
  public function validateUrl(array $announcement): bool {
    if ($announcement['link']) {
      $host = parse_url($announcement['link'], PHP_URL_HOST);
      if ($host) {
        // First character can only be a letter or a digit.
        // @see https://www.rfc-editor.org/rfc/rfc1123#page-13
        if (preg_match('/^([a-zA-Z0-9][a-zA-Z0-9\-_]*\.)?drupal\.org$/', $host)) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Fetches the feed either from a local cache or fresh remotely.
   *
   * The structure of an announcement in the feed is:
   *   - id: Id.
   *   - title: Title of the announcement.
   *   - teaser: Announcement teaser.
   *   - link: URL
   *   - sticky: 1 if featured, 0 if not featured.
   *   - version: Target version of Drupal as a Composer version constraint.
   *   - updated: Last updated timestamp.
   *
   * @return array
   *   An array of announcements from the feed relevant to the Drupal version.
   *   The array is empty if there were no matching announcements. If an error
   *   occurred while fetching/decoding the feed its thrown as an exception.
   *
   * @throws \Exception
   */
  public function fetch(): array {
    $announcements = $this->tempStore->get('announcements');
    if ($announcements === NULL) {
      try {
        $feed_content = (string) $this->httpClient->get($this->feedUrl)->getBody();
      }
      catch (\Exception $e) {
        $this->logger->error($e->getMessage());
        throw $e;
      }

      try {
        $announcements = Json::decode($feed_content);
      }
      catch (\Exception $e) {
        $this->logger->error($e->getMessage());
        throw $e;
      }
      // Ensure that announcements reference drupal.org.
      $announcements = array_filter($announcements, [$this, 'validateUrl']);
      $this->tempStore->setWithExpire('announcements', $announcements,
        $this->config->get('max_age'));
    }

    // Filter to announcements applicable to the current Drupal version.
    $announcements = array_filter($announcements, [$this, 'isRelevantItem']);

    $sticky_announcements = [];
    $non_sticky = [];
    foreach ($announcements as $announcement) {
      if ($announcement['sticky']) {
        $sticky_announcements[] = $announcement;
      }
      else {
        $non_sticky[] = $announcement;
      }
    }

    if (count($sticky_announcements) >= 10) {
      return array_slice($sticky_announcements, 0, 10);
    }
    else {
      return array_slice(array_merge($sticky_announcements, $non_sticky), 0, 10);
    }
  }

}
