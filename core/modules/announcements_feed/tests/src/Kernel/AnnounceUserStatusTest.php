<?php

namespace Drupal\Tests\announcements_feed\Kernel;

use Drupal\KernelTests\KernelTestBase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * @coversDefaultClass \Drupal\system\SecurityAdvisories\SecurityAdvisoriesFetcher
 *
 * @group announcements_feed
 */
class AnnounceUserStatusTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'toolbar',
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
    $this->installSchema('user', ['users_data']);

    // Setting current user.
    $permissions = [
      'access toolbar',
      'access announcements',
    ];
    $this->setUpCurrentUser(['uid' => 1], $permissions);
  }

  /**
   * Tests testAllAnnouncements should get all announcements.
   *
   * First time accessing the announcements.
   */
  public function testAllAnnouncementsFirst(): void {

    $feed_item = $this->providerShowAnnouncements();
    $this->setFeedItems($feed_item);

    // First time access.
    $feeds = $this->fetchFeedItems();
    $all_items = $feeds->getAllAnnouncements();
    $this->assertCount(4, $all_items);

    // Checking the 'new' status is enabled or not.
    $this->assertSame($all_items[0]['id'], $all_items[0]['new']);
    $this->assertCount(3, $this->history);

    $this->setFeedItems($feed_item);

    // Second time access.
    $feeds = $this->fetchFeedItems();
    $all_items = $feeds->getAllAnnouncements();
    $this->assertCount(4, $all_items);

    // Checking the 'new' status is disabled or not.
    $this->assertSame('', $all_items[0]['new']);
    $this->assertCount(5, $this->history);

    // Change user.
    // Setting current user.
    $permissions = [
      'access toolbar',
      'access announcements',
    ];
    $this->setUpCurrentUser(['uid' => 2], $permissions);
    $this->setFeedItems($feed_item);

    // First time access.
    $feeds = $this->fetchFeedItems();
    $all_items = $feeds->getAllAnnouncements();
    $this->assertCount(4, $all_items);

    // Checking the 'new' status is enabled or not.
    $this->assertSame($all_items[0]['id'], $all_items[0]['new']);
    $this->assertCount(8, $this->history);

    // Check after adding new record.
    $feed_item = $this->providerShowUpdatedAnnouncements();
    $this->setFeedItems($feed_item);
    $feeds = $this->fetchFeedItems();
    $all_items = $feeds->getAllAnnouncements();
    $this->assertCount(5, $all_items);
    $this->assertSame($all_items[0]['id'], 1005);

    // Checking the 'new' status is enabled or not.
    $this->assertSame($all_items[0]['id'], $all_items[0]['new']);
    $this->assertCount(11, $this->history);

  }

  /**
   * Data provider for testAllAnnouncements().
   */
  public function providerShowAnnouncements(): array {
    return [
      [
        'id' => 1001,
        'title' => 'Drupal security update Test',
        'link' => 'https://www.drupal.org/project/announce',
        'teaser' => 'Test teaser 1',
        'sticky' => 1,
        'version' => '^10',
        'updated' => 1611041378,
      ],
      [
        'id' => 1002,
        'title' => 'Drupal security update Test',
        'link' => 'https://www.drupal.org/project/announce',
        'teaser' => 'Test teaser 2',
        'sticky' => 1,
        'version' => '^10',
        'updated' => 1611041378,
      ],
      [

        'id' => 1003,
        'title' => 'Drupal security update Test',
        'link' => 'https://www.drupal.org/project/announce',
        'teaser' => 'Test teaser 3',
        'sticky' => 1,
        'version' => '^10',
        'updated' => 1611041378,
      ],
      [
        'id' => 1004,
        'title' => 'Drupal security update Test',
        'link' => 'https://www.drupal.org/project/announce',
        'teaser' => 'Test teaser 4',
        'sticky' => 1,
        'version' => '^10',
        'updated' => 1611041378,
      ],
    ];
  }

  /**
   * Data provider for testAllAnnouncements().
   */
  public function providerShowUpdatedAnnouncements(): array {
    return [

      [
        'id' => 1005,
        'title' => 'Drupal security update Test new',
        'link' => 'https://www.drupal.org/project/announce',
        'teaser' => 'Test teaser 1',
        'sticky' => 1,
        'version' => '^10',
        'updated' => 1611041378,
      ],
      [
        'id' => 1001,
        'title' => 'Drupal security update Test',
        'link' => 'https://www.drupal.org/project/announce',
        'teaser' => 'Test teaser 1',
        'sticky' => 1,
        'version' => '^10',
        'updated' => 1611041378,
      ],
      [
        'id' => 1002,
        'title' => 'Drupal security update Test',
        'link' => 'https://www.drupal.org/project/announce',
        'teaser' => 'Test teaser 2',
        'sticky' => 1,
        'version' => '^10',
        'updated' => 1611041378,
      ],
      [

        'id' => 1003,
        'title' => 'Drupal security update Test',
        'link' => 'https://www.drupal.org/project/announce',
        'teaser' => 'Test teaser 3',
        'sticky' => 1,
        'version' => '^10',
        'updated' => 1611041378,
      ],
      [
        'id' => 1004,
        'title' => 'Drupal security update Test',
        'link' => 'https://www.drupal.org/project/announce',
        'teaser' => 'Test teaser 4',
        'sticky' => 1,
        'version' => '^10',
        'updated' => 1611041378,
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
    $responses[] = new Response('200', [], json_encode($feed_items));
    $responses[] = new Response('200', [], json_encode($feed_items));
    $responses[] = new Response('200', [], json_encode($feed_items));

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
    // Rebuild the container because the 'system.sa_fetcher' service and other
    // services may already have an instantiated instance of the 'http_client'
    // service without these changes.
    $this->container->get('kernel')->rebuildContainer();
    $this->container = $this->container->get('kernel')->getContainer();
    $this->container->set('http_client', new Client(['handler' => $handler_stack]));
  }

  /**
   * Gets the user_status object from the announcements_feed service.
   */
  protected function fetchFeedItems(): ?object {
    $user_status = $this->container->get('announcements_feed.user_status');
    return $user_status;
  }

}
