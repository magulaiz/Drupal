<?php

namespace Drupal\Tests\announcements_feed\FunctionalJavascript;

use Drupal\Tests\system\FunctionalJavascript\OffCanvasTestBase;
use Drupal\announce_feed_test\AnnounceTestHttpClient;

/**
 * Test the access announcement according to json feed changes.
 *
 * @group announcements_feed
 */
class AlertsJsonFeedTest extends OffCanvasTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'toolbar',
    'announcements_feed',
    'announce_feed_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A test endpoint which contains the community feeds.
   *
   * @var string
   */
  protected $responseJson;

  /**
   * A test endpoint which include the new feeds.
   *
   * @var string
   */
  protected $updatedJson;

  /**
   * A test endpoint which displays an empty json.
   *
   * @var string
   */
  protected $emptyJson;

  /**
   * A test endpoint that will have some feeds removed.
   *
   * @var string
   */
  protected $removed;

  /**
   * A user with permission to access toolbar and access announcements.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user1;

  /**
   * A user with permission to access toolbar and access announcements.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user2;

  /**
   * A user with permission to access toolbar and access announcements.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user3;

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    parent::setUp();
    $this->responseJson = $this->buildUrl('/announce-feed-json/community-feeds');
    $this->updatedJson = $this->buildUrl('/announce-feed-json/updated');
    $this->emptyJson = $this->buildUrl('/announce-feed-json/empty');
    $this->removed = $this->buildUrl('/announce-feed-json/removed');
    $this->user1 = $this->drupalCreateUser(
      [
        'access toolbar',
        'access announcements',
      ]
    );

    $this->user2 = $this->drupalCreateUser(
      [
        'access toolbar',
        'access announcements',
      ]
    );

    $this->user3 = $this->drupalCreateUser(
      [
        'access toolbar',
        'access announcements',
      ]
    );
    AnnounceTestHttpClient::setAnnounceTestEndpoint($this->responseJson);
  }

  /**
   * Check the status of the red dot alert with an updated JSON feed URL.
   */
  public function testAnnounceFeedUpdated() {

    $this->drupalLogin($this->user2);
    $this->drupalGet('<front>');
    $this->clickLink('Announcements');
    $this->drupalLogout();

    $this->drupalLogin($this->user1);
    $this->drupalGet('<front>');
    $this->clickLink('Announcements');
    $this->waitForOffCanvasToOpen();

    $this->drupalGet('<front>');
    $this->assertSession()->elementNotExists('css', '.announce-new');

    // Change the feed url and reset temp storage.
    AnnounceTestHttpClient::setAnnounceTestEndpoint($this->updatedJson);
    drupal_flush_all_caches();

    $this->drupalGet('<front>');

    // Assert the '.announce-new' class is added when there are new
    // announcements.
    // Alert Icon should display a red dot over it.
    $this->assertSession()->elementExists('css', '.announce-new');

    // The new items in the feed should show as unread.
    // Old items should show as read.
    $this->clickLink('Announcements');
    $this->waitForOffCanvasToOpen();

    $this->assertSession()->elementExists('css', '.announcement__new');
    $total_updated_records_count = 1;
    // Checking existence of the unread record.
    $this->assertSession()->elementsCount('css', '.announcement__new', $total_updated_records_count);
    $page = $this->getSession()->getPage();
    $unread_status = $page->find('css', '.announcement__new');
    $this->assertNotEmpty($unread_status);
    $this->assertStringContainsString('Only 10 - Drupal 106 is available and this feed is Updated', $unread_status->getParent()->getText());

    // Access the alert icon again.
    $this->drupalLogout();

    // Login user second time.
    $this->drupalLogin($this->user1);

    // Alert Icon should not display a red dot over it.
    $this->drupalGet('<front>');
    $this->assertSession()->elementNotExists('css', '.announce-new');

    // All items should show as read.
    $this->clickLink('Announcements');
    $this->waitForOffCanvasToOpen();
    $this->assertSession()->elementNotExists('css', '.announcement__new');
    $this->drupalLogout();

    // Login as another user and access the alert icon.
    $this->drupalLogin($this->user2);
    $this->drupalGet('<front>');

    // Alert Icon should display a red dot over it.
    $this->assertSession()->elementExists('css', '.announce-new');

    // The new items in the feed should show as unread.
    // Old items should show as read.
    $this->clickLink('Announcements');
    $this->waitForOffCanvasToOpen();
    $this->assertSession()->elementExists('css', '.announcement__new');

    $total_updated_records_count = 1;

    // Checking existence of the unread record.
    $this->assertSession()->elementsCount('css', '.announcement__new', $total_updated_records_count);

    // The new items for that user should be shown as unread.
    $page = $this->getSession()->getPage();
    $unread_status = $page->find('css', '.announcement__new');
    $this->assertNotEmpty($unread_status);
    $this->assertStringContainsString('Only 10 - Drupal 106 is available and this feed is Updated', $unread_status->getParent()->getText());

    // Checking updated title.
    $new_page_html = $page->getHtml();
    $this->assertStringContainsString('announce title updated', $new_page_html);

    $this->drupalLogout();
  }

  /**
   * Check the status of the red dot icon when the feed is updated.
   */
  public function testAnnounceFeedRemoved() {

    $this->drupalLogin($this->user3);
    $this->drupalGet('<front>');
    $this->clickLink('Announcements');
    $this->waitForOffCanvasToOpen();
    $this->assertSession()->elementNotExists('css', '.announce-new');
    $this->drupalGet('<front>');
    $this->clickLink('Announcements');
    $this->waitForOffCanvasToOpen();
    $this->assertSession()->elementNotExists('css', '.announcement__new');
    $page = $this->getSession()->getPage();
    $new_page_html = $page->getHtml();
    $this->assertStringNotContainsString('Only 10 - Drupal 106 is available and this feed is Updated', $new_page_html);

    // Change the feed url and reset temp storage.
    AnnounceTestHttpClient::setAnnounceTestEndpoint($this->updatedJson);
    drupal_flush_all_caches();

    $this->drupalGet('<front>');
    $this->assertSession()->elementExists('css', '.announce-new');
    $this->clickLink('Announcements');
    $this->waitForOffCanvasToOpen();
    $this->assertSession()->elementsCount('css', '.announcement__new', 1);
    $page = $this->getSession()->getPage();
    $unread_status = $page->find('css', '.announcement__new');
    $this->assertNotEmpty($unread_status);
    $this->assertStringContainsString('Only 10 - Drupal 106 is available and this feed is Updated', $unread_status->getParent()->getText());
    $this->drupalLogout();

    // Change the feed url and reset temp storage.
    AnnounceTestHttpClient::setAnnounceTestEndpoint($this->removed);
    drupal_flush_all_caches();
    $this->drupalLogin($this->user3);

    // If the removed item is only item the
    // user hasn't read the the red dot should not show.
    $this->drupalGet('<front>');
    $this->assertSession()->elementNotExists('css', '.announce-new');

    // Removed items should not display in the announcement model.
    $this->clickLink('Announcements');
    $this->waitForOffCanvasToOpen();
    $this->assertSession()->elementNotExists('css', '.announcement__new');
    $page = $this->getSession()->getPage();
    $new_page_html = $page->getHtml();
    $this->assertStringNotContainsString('Only 10 - Drupal 106 is available and this feed is Updated', $new_page_html);

  }

  /**
   * Check the status of the red dot alert with an empty JSON feed.
   */
  public function testAnnounceFeedEmpty() {
    // Change the feed url and reset temp storage.
    AnnounceTestHttpClient::setAnnounceTestEndpoint($this->emptyJson);
    drupal_flush_all_caches();

    $this->drupalLogin($this->user3);
    $this->drupalGet('<front>');
    $this->assertSession()->elementNotExists('css', '.announce-new');

    // Removed items should not display in the announcement model.
    $this->clickLink('Announcements');
    $this->waitForOffCanvasToOpen();
    $this->assertSession()->elementNotExists('css', '.announcement__new');
    $page = $this->getSession()->getPage();
    $new_page_html = $page->getHtml();
    $this->assertStringContainsString('No announcements available', $new_page_html);

  }

}
