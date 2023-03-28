<?php

namespace Drupal\announce_feed_test;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Provides a decorator service for the 'http_client' service for testing.
 */
class AnnounceTestHttpClient implements ClientInterface {

  /**
   * Constructs an AdvisoriesTestHttpClient object.
   *
   * @param \GuzzleHttp\Client $innerClient
   *   The decorated http_client service.
   */
  public function __construct(protected Client $innerClient) {
  }

  /**
   * {@inheritdoc}
   */
  public function get($uri, array $options = []): ResponseInterface {
    $test_end_point = \Drupal::state()->get('announce_test_endpoint');
    if ($test_end_point && strpos($uri, '://www.drupal.org/announcements.json') !== FALSE) {
      // Only override $uri if it matches the advisories JSON feed to avoid
      // changing any other uses of the 'http_client' service during tests with
      // this module installed.
      $uri = $test_end_point;
    }
    return $this->innerClient->get($uri, $options);
  }

  /**
   * Sets the test endpoint for the advisories JSON feed.
   *
   * @param string $test_endpoint
   *   The test endpoint.
   */
  public static function setAnnounceTestEndpoint(string $test_endpoint): void {
    \Drupal::state()->set('announce_test_endpoint', $test_endpoint);
    \Drupal::service('keyvalue.expirable')->get('announcements_feed')->delete('announcements');
    \Drupal::service('user.data')->delete('announcements_feed');
  }

  /**
   * {@inheritdoc}
   */
  public function __call($name, $arguments) {
    return $this->innerClient->__call($name, $arguments);
  }

}
