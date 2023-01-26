<?php

namespace Drupal\announcements_feed;

use Composer\Semver\Semver;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueExpirableFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Service to fetch announcements from the external feed.
 */
class AnnounceFetcher {

  use StringTranslationTrait;

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
    return array_column($announcements, 'id');
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
  protected static function isRelevantItem(array $announcement): bool {
    return isset($announcement['_extra']['version'])
      && Semver::satisfies(\Drupal::VERSION, $announcement['_extra']['version']);
  }

  /**
   * Check whether the link of announcement controlled by D.O.
   *
   * @param array $announcement
   *   Announcement feed item to check.
   *
   * @return bool
   *   Return True if $announcement['url'] is controlled by the D.O.
   */
  public static function validateUrl(array $announcement): bool {
    if (!$announcement['url']) {
      return FALSE;
    }
    $host = parse_url($announcement['url'], PHP_URL_HOST);

    // First character can only be a letter or a digit.
    // @see https://www.rfc-editor.org/rfc/rfc1123#page-13
    return $host && preg_match('/^([a-zA-Z0-9][a-zA-Z0-9\-_]*\.)?drupal\.org$/', $host);
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
   *   - _extra.featured: 1 if featured, 0 if not featured.
   *   - _extra.version: Target version of Drupal, as a Composer version.
   *
   * @return array
   *   An array of announcements from the feed relevant to the Drupal version.
   *   The array is empty if there were no matching announcements. If an error
   *   occurred while fetching/decoding the feed, it is thrown as an exception.
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

      if (!isset($announcements['items'])) {
        $this->logger->error($this->t('The feed format is not valid.'));
        throw new \Exception('Invalid format');
      }

      $announcements = $announcements['items'] ?? [];
      // Ensure that announcements reference drupal.org and are applicable to
      // the current Drupal version.
      $announcements = array_filter($announcements, function (array $announcement) {
        return static::validateUrl($announcement) && static::isRelevantItem($announcement);
      });

      $this->tempStore->setWithExpire('announcements', $announcements,
        $this->config->get('max_age'));
    }

    // Put all the sticky announcements before the rest.
    $prioritized = [[], []];
    foreach ($announcements as $announcement) {
      $prioritized[$announcement['_extra']['featured'] ? 0 : 1][] = $announcement;
    }
    $announcements = array_merge($prioritized[0], $prioritized[1]);

    return array_slice($announcements, 0, 10);
  }

}
