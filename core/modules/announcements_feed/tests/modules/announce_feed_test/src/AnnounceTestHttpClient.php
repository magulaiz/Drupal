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
   * The decorated http_client service.
   *
   * @var \GuzzleHttp\Client
   */
  protected $innerClient;

  /**
   * Constructs an AdvisoriesTestHttpClient object.
   *
   * @param \GuzzleHttp\Client $client
   *   The decorated http_client service.
   */
  public function __construct(Client $client) {
    $this->innerClient = $client;
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
  }

  /**
   * {@inheritdoc}
   */
  public function __call($name, $arguments) {
    return $this->innerClient->__call($name, $arguments);
  }

  /**
   * {@inheritdoc}
   */
  public function send(RequestInterface $request, array $options = []): ResponseInterface {
    return $this->innerClient->send($request, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface {
    return $this->innerClient->sendAsync($request, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function request($method, $uri, array $options = []): ResponseInterface {
    return $this->innerClient->request($method, $uri, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function requestAsync($method, $uri, array $options = []): PromiseInterface {
    return $this->innerClient->request($method, $uri, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig($option = NULL) {
    return $this->innerClient->getConfig($option);
  }

}
