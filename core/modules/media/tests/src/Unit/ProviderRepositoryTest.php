<?php

namespace Drupal\Tests\media\Unit;

use Drupal\Core\KeyValueStore\KeyValueMemoryFactory;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\media\OEmbed\ProviderException;
use Drupal\media\OEmbed\ProviderRepository;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * @coversDefaultClass \Drupal\media\OEmbed\ProviderRepository
 *
 * @group media
 */
class ProviderRepositoryTest extends UnitTestCase {

  /**
   * The provider repository under test.
   *
   * @var \Drupal\media\OEmbed\ProviderRepository
   */
  private $providerRepository;

  /**
   * The HTTP client handler which will serve responses.
   *
   * @var \GuzzleHttp\Handler\MockHandler
   */
  private $responses;

  /**
   * The key-value store factory.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueMemoryFactory
   */
  private $keyValueFactory;

  /**
   * The time that the current test began.
   *
   * @var int
   */
  private $currentTime;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $config_factory = $this->getConfigFactoryStub([
      'media.settings' => [
        'oembed_providers_url' => 'https://oembed.com/providers.json',
      ],
    ]);
    $this->keyValueFactory = new KeyValueMemoryFactory();
    $this->currentTime = time();
    $time = $this->prophesize('\Drupal\Component\Datetime\TimeInterface');
    $time->getCurrentTime()->willReturn($this->currentTime);

    $this->responses = new MockHandler();
    $client = new Client([
      'handler' => HandlerStack::create($this->responses),
    ]);
    $this->providerRepository = new ProviderRepository(
      $client,
      $config_factory,
      $time->reveal(),
      $this->keyValueFactory,
      new LoggerChannelFactory()
    );
  }

  /**
   * Tests that a successful fetch stores the provider database in key-value.
   */
  public function testSuccessfulFetch(): void {
    $body = <<<END
[
  {
    "provider_name": "YouTube",
    "provider_url": "https:\/\/www.youtube.com\/",
    "endpoints": [
      {
        "schemes": [
          "https:\/\/*.youtube.com\/watch*",
          "https:\/\/*.youtube.com\/v\/*",
          "https:\/\/youtu.be\/*"
        ],
        "url": "https:\/\/www.youtube.com\/oembed",
        "discovery": true
      }
    ]
  }
]
END;
    $response = new Response(200, [], $body);
    $this->responses->append($response);

    $provider = $this->providerRepository->get('YouTube');
    $stored_data = [
      'data' => [
        'YouTube' => $provider,
      ],
      'expires' => $this->currentTime + 604800,
    ];
    $this->assertSame($stored_data, $this->keyValueFactory->get('media')->get('oembed_providers'));
  }

  /**
   * Tests that an invalid response returns the data stored in key-value.
   *
   * @dataProvider providerExpired
   */
  public function testInvalidResponse(int $expiration_offset): void {
    $provider = $this->prophesize('\Drupal\media\OEmbed\Provider')
      ->reveal();

    $this->keyValueFactory->get('media')
      ->set('oembed_providers', [
        'data' => [
          'YouTube' => $provider,
        ],
        'expires' => $this->currentTime + $expiration_offset,
      ]);

    $response = new Response(200, [], "This certainly isn't valid JSON.");
    $this->responses->append($response);
    $this->assertSame($provider, $this->providerRepository->get('YouTube'));
  }

  /**
   * Tests that an invalid response throws if there is no data in key-value.
   */
  public function testInvalidResponseWithoutStoredData(): void {
    $response = new Response(200, [], "Most definitely an invalid response.");
    $this->responses->append($response);
    $this->expectException(ProviderException::class);
    $this->providerRepository->get('YouTube');
  }

  /**
   * Tests that a request exception returns the data stored in key-value.
   */
  public function testRequestException(): void {
    $provider = $this->prophesize('\Drupal\media\OEmbed\Provider')
      ->reveal();

    // This data is expired (stale), but it should be returned anyway.
    $this->keyValueFactory->get('media')
      ->set('oembed_providers', [
        'data' => [
          'YouTube' => $provider,
        ],
        'expires' => $this->currentTime - 86400,
      ]);

    $response = new Response(503);
    $this->responses->append($response);
    $this->assertSame($provider, $this->providerRepository->get('YouTube'));
  }

  /**
   * Tests that an exception is thrown if there is no data in key-value.
   */
  public function testRequestExceptionWithoutStoredData(): void {
    $response = new Response(418);
    $this->responses->append($response);
    $this->expectException(ProviderException::class);
    $this->providerRepository->get('YouTube');
  }

  /**
   * Tests a successful fetch but with a single corrupt item.
   */
  public function testCorruptProviderIgnored(): void {
    $body = <<<END
[
  {
    "provider_name": "YouTube",
    "provider_url": "https:\/\/www.youtube.com\/",
    "endpoints": [
      {
        "schemes": [
          "https:\/\/*.youtube.com\/watch*",
          "https:\/\/*.youtube.com\/v\/*",
          "https:\/\/youtu.be\/*"
        ],
        "url": "https:\/\/www.youtube.com\/oembed",
        "discovery": true
      }
    ]
  },
  {
    "provider_name": "Uncle Rico's football videos",
    "provider_url": "not a real url",
    "endpoints": []
  }
]
END;
    $response = new Response(200, [], $body);
    $this->responses->append($response);

    $youtube = $this->providerRepository->get('YouTube');
    // The corrupt provider should not be stored.
    $stored_data = [
      'data' => [
        'YouTube' => $youtube,
      ],
      'expires' => $this->currentTime + 604800,
    ];
    $this->assertSame($stored_data, $this->keyValueFactory->get('media')->get('oembed_providers'));

    $this->expectException('InvalidArgumentException');
    $this->providerRepository->get("Uncle Rico's football videos");
  }

  /**
   * Data provider.
   *
   * @return array
   *   Test cases.
   */
  public function providerExpired() : array {
    return [
      'expired' => [-86400],
      'fresh' => [86400],
    ];
  }

}
