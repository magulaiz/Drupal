<?php

declare(strict_types=1);

namespace Drupal\Tests\navigation\Functional;

use Drupal\Core\Url;
use Drupal\Tests\system\Functional\Cache\AssertPageCacheContextsAndTagsTrait;
use Drupal\Tests\system\Functional\Cache\PageCacheTagsTestBase;

/**
 * Tests the missing menu icon abbreviation functionality.
 *
 * @group navigation
 */
class NavigationSidebarTest extends PageCacheTagsTestBase {

  use AssertPageCacheContextsAndTagsTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['navigation', 'node', 'announcements_feed', 'test_page_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * An admin user to configure the test environment.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Create and log in an administrative user.
    $this->adminUser = $this->drupalCreateUser([
      'administer navigation_block',
      'administer site configuration',
      'access administration pages',
      'access navigation',
      'bypass node access',
      'access announcements',
    ]);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests the abbreviation visibility.
   */
  public function testAbbrVisibility(): void {
    $test_page_url = Url::fromRoute('test_page_test.test_page');
    $this->drupalGet($test_page_url);

    $this->assertSession()->elementExists('xpath', "//a[contains(@class, 'toolbar-button--icon--announcements-feed-announcement')]/span");
    $this->assertSession()->elementTextEquals('xpath', "//a[contains(@class, 'toolbar-button--icon--announcements-feed-announcement')]/span/span", 'AN');
  }

}
