<?php

namespace Drupal\Tests\announcements_feed\Kernel;

use Drupal\KernelTests\KernelTestBase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

/**
 * @coversDefaultClass \Drupal\announcements_feed\AnnounceFetcher
 *
 * @group announcements_feed
 */
class AnnounceFetcherTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'system',
    'announcements_feed',
  ];

  /**
   * History of requests/responses.
   *
   * @var array
   */
  protected $history = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig('system');
    $this->installConfig(['user']);
    $this->installConfig(['announcements_feed']);
  }

  /**
   * Tests announcement that should be displayed.
   *
   * @param mixed[] $feed_item
   *   The feed item to test. 'title' and 'link' are omitted from this array
   *   because they do not need to vary between test cases.
   *
   * @dataProvider providerShowAnnouncements
   */
  public function testShowAnnouncements(array $feed_item): void {
    $this->setFeedItems([$feed_item]);
    $feeds = $this->fetchFeedItems();
    $this->assertCount(1, $feeds);
    $this->assertSame('https://www.drupal.org/project/announce', $feeds[0]['link']);
    $this->assertSame('Drupal security update Test', $feeds[0]['title']);
    $this->assertSame('^10', $feeds[0]['version']);
    $this->assertCount(1, $this->history);
  }

  /**
   * Tests feed fields.
   */
  public function testFeedFields(): void {
    $feed_item_1 = [
      'id' => 1001,
      'content_html' => 'Test teaser 1',
      'url' => 'https://www.drupal.org/project/announce',
      '_extra' => [
        'featured' => 1,
        'version' => '^10',
      ],
      'date_modified' => "2021-09-02T15:09:42+00:00",
    ];
    $this->setFeedItems([$feed_item_1]);
    $feeds = $this->fetchFeedItems();
    $this->assertCount(1, $feeds);
    $this->assertSame($feed_item_1['id'], $feeds[0]['id']);
    $this->assertSame($feed_item_1['content_html'], $feeds[0]['content_html']);
    $this->assertSame($feed_item_1['_extra']['featured'], $feeds[0]['_extra']['featured']);
    $this->assertSame($feed_item_1['date_modified'], $feeds[0]['date_modified']);
    $this->assertSame($feed_item_1['_extra']['version'], $feeds[0]['_extra']['version']);
  }

  /**
   * Data provider for testShowAnnouncements().
   */
  public function providerShowAnnouncements(): array {
    return [
      '1' => [
        'feed_item' => [
          'id' => 1001,
          'content_html' => 'Test teaser 1',
          '_extra' => [
            'featured' => 1,
            'version' => '^10',
          ],
          'date_modified' => "2021-09-02T15:09:42+00:00",
        ],
      ],
      '2' => [
        'feed_item' => [
          'id' => 1002,
          'content_html' => 'Test teaser 2',
          '_extra' => [
            'featured' => 1,
            'version' => '^10',
          ],
          'date_modified' => "2021-09-02T15:09:42+00:00",
        ],
      ],
      '3' => [
        'feed_item' => [
          'id' => 1003,
          'content_html' => 'Test teaser 3',
          '_extra' => [
            'featured' => 1,
            'version' => '^10',
          ],
          'date_modified' => "2021-09-02T15:09:42+00:00",
        ],
      ],
      '4' => [
        'feed_item' => [
          'id' => 1004,
          'content_html' => 'Test teaser 4',
          '_extra' => [
            'featured' => 1,
            'version' => '^10',
          ],
          'date_modified' => "2021-09-02T15:09:42+00:00",
        ],
      ],
    ];
  }

  /**
   * Sets the feed items to be returned for the test.
   *
   * @param mixed[][] $feed_items
   *   The feeds items to test. Every time the http_client makes a request the
   *   next item in this array will be returned. For each feed item 'title' and
   *   'link' are omitted because they do not need to vary between test cases.
   */
  protected function setFeedItems(array $feed_items): void {
    $responses = [];
    foreach ($feed_items as $feed_item) {
      $feed_item += [
        'title' => 'Drupal security update Test',
        'url' => 'https://www.drupal.org/project/announce',
      ];
      $responses[] = new Response('200', [], json_encode([$feed_item]));
    }
    $this->setTestFeedResponses($responses);
  }

  /**
   * Sets test feed responses.
   *
   * @param \GuzzleHttp\Psr7\Response[] $responses
   *   The responses for the http_client service to return.
   */
  protected function setTestFeedResponses(array $responses): void {
    // Create a mock and queue responses.
    $mock = new MockHandler($responses);
    $handler_stack = HandlerStack::create($mock);
    $history = Middleware::history($this->history);
    $handler_stack->push($history);
    // Rebuild the container because the 'announce.fetcher' service and other
    // services may already have an instantiated instance of the 'http_client'
    // service without these changes.
    $this->container->get('kernel')->rebuildContainer();
    $this->container = $this->container->get('kernel')->getContainer();
    $this->container->set('http_client', new Client(['handler' => $handler_stack]));
  }

  /**
   * Gets the announcements from the 'announce.fetcher' service.
   *
   * @return \Drupal\announcements_feed\AnnounceFetcher\fetch[]|null
   *   The return value of AnnounceFetcher::fetch().
   */
  protected function fetchFeedItems(): ?array {
    $fetcher = $this->container->get('announcements_feed.fetcher');
    return $fetcher->fetch();
  }

}
