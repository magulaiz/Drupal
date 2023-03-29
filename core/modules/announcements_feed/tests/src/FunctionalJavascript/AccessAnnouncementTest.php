<?php

namespace Drupal\Tests\announcements_feed\FunctionalJavascript;

use Drupal\Tests\system\FunctionalJavascript\OffCanvasTestBase;
use Drupal\announce_feed_test\AnnounceTestHttpClientMiddleware;

/**
 * Test the access announcement permissions to get access announcement icon.
 *
 * @group announcements_feed
 */
class AccessAnnouncementTest extends OffCanvasTestBase {

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
   * {@inheritdoc}
   */
  public function setUp():void {
    parent::setUp();
    $response_json = $this->buildUrl('/announce-feed-json/community-feeds');
    AnnounceTestHttpClientMiddleware::setAnnounceTestEndpoint($response_json);
  }

  /**
   * Test of viewing announcements by a user with appropriate permission.
   */
  public function testAnnounceFirstLogin() {
    $this->drupalLogin(
      $this->drupalCreateUser(
        [
          'access toolbar',
          'access announcements',
        ]
      )
    );

    $this->drupalGet('<front>');

    // Alert Icon should display a red dot over it.
    $this->assertSession()->elementExists('css', '.announce-new');

    // All alerts items should display as unread alerts,
    // having darker background and dot icon to indicate unread.
    $this->clickLink('Announcements');
    $this->waitForOffCanvasToOpen();
    $this->assertSession()->elementExists('css', '.announcement__new');
    $title_count = count($this->getSession()->getPage()->findAll('css', '.announcement__title'));
    $this->assertSession()->elementsCount('css', '.announcement__new', $title_count);
  }

  /**
   * Testing on Second time login.
   *
   * Tests for access announce red dot icon.
   *
   * Unread/Read status of alerts in announce panel.
   *
   * Check status with new user to test cache system.
   */
  public function testAnnounceSecondLogin() {
    // Create user with announce permissions.
    $account = $this->drupalCreateUser(
      [
        'access toolbar',
        'access announcements',
      ]
    );
    $this->drupalLogin($account);
    $this->drupalGet('<front>');
    $this->clickLink('Announcements');
    $this->drupalLogout();
    // Login user second time.
    $this->drupalLogin($account);
    $this->drupalGet('<front>');

    // Alert Icon should not display a red dot over it.
    $this->assertSession()->elementNotExists('css', '.announcement__new');

    // All alerts items should display as unread alerts,
    // having darker background and dot icon to indicate unread.
    $this->clickLink('Announcements');
    $this->waitForOffCanvasToOpen();

    // All alerts icons should display as read alerts,
    // having light background and no dot icon.
    $this->assertSession()->elementNotExists('css', '.announcement__new');
    $this->drupalLogout();

    // Login with new user with announce permissions.
    $account = $this->drupalCreateUser(
      [
        'access toolbar',
        'access announcements',
      ]
    );
    $this->drupalLogin($account);
    $this->drupalGet('<front>');

    // Test if class of red dot icon exist.
    // Alert Icon should display a red dot over it.
    $this->assertSession()->elementExists('css', '.announce-new');

    // All should be shown as unread.
    $this->clickLink('Announcements');
    $this->waitForOffCanvasToOpen();
    $this->assertSession()->elementExists('css', '.announcement__new');
    $title_count = count($this->getSession()->getPage()->findAll('css', '.announcement__title'));
    $this->assertSession()->elementsCount('css', '.announcement__new', $title_count);
    $this->drupalLogout();

  }

  /**
   * Testing announce icon without announce permission.
   */
  public function testAnnounceWithoutPermission() {
    // User without "access announcements" permission.
    $account = $this->drupalCreateUser(
      [
        'access toolbar',
      ]
    );
    $this->drupalLogin($account);
    $this->drupalGet('<front>');

    // Check that the user can see the toolbar.
    $this->assertSession()->elementExists('css', '#toolbar-bar');

    // But not the announcements.
    $this->assertSession()->elementNotExists('css', '.toolbar-icon-announce');

    $this->drupalGet('admin/announcements_feed');
    $this->assertSession()->responseContains('You are not authorized to access this page.');
  }

}
