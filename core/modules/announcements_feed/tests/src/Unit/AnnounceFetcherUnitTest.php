<?php

namespace Drupal\Tests\announcements_feed\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\announcements_feed\AnnounceFetcher;

/**
 * Simple test to ensure that asserts pass.
 *
 * @group announcements_feed
 */
class AnnounceFetcherUnitTest extends UnitTestCase {

  /**
   * The Fetcher service object.
   *
   * @var \Drupal\announcements_feed\AnnounceFetcher
   */
  protected $fetcher;

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->httpClient = $this->createMock('GuzzleHttp\ClientInterface');
    $this->config = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    $this->tempStore = $this->createMock('Drupal\Core\KeyValueStore\KeyValueExpirableFactory');
    $this->logger = $this->createMock('Psr\Log\LoggerInterface');
    $this->fetcher = new AnnounceFetcher($this->httpClient, $this->config, $this->tempStore, $this->logger, 'https://www.drupal.org/announcements.json');
  }

  /**
   * Test the ValidateUrl() method.
   *
   * @covers Drupal\announcements_feed\AnnounceFetcher::validateUrl
   *
   * @dataProvider urlProvider
   */
  public function testValidateUrl($url, $isValid) {
    $this->assertEquals($isValid, $this->fetcher->validateUrl(['link' => $url]));
  }

  /**
   * Data for the testValidateUrl.
   */
  public function urlProvider() {
    return [
      ['https://www.drupal.org', TRUE],
      ['https://drupal.org', TRUE],
      ['https://api.drupal.org', TRUE],
      ['https://a.drupal.org', TRUE],
      ['https://123.drupal.org', TRUE],
      ['https://api-new.drupal.org', TRUE],
      ['https://api_new.drupal.org', TRUE],
      ['https://api-.drupal.org', TRUE],
      ['https://www.example.org', FALSE],
      ['https://example.org', FALSE],
      ['https://api.example.org/project/announce', FALSE],
      ['https://-api.drupal.org', FALSE],
      ['https://a.example.org/project/announce', FALSE],
      ['https://test.drupaal.com', FALSE],
      ['https://api.drupal.org.example.com', FALSE],
      ['https://example.org/drupal.org', FALSE],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown(): void {
    unset($this->fetcher);
  }

}
