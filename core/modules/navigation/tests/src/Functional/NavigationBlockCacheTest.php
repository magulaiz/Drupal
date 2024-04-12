<?php

declare(strict_types=1);

namespace Drupal\Tests\navigation\Functional;

use Drupal\Core\Cache\Cache;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\navigation\Traits\NavigationBlockCreationTrait;

/**
 * Tests navigation block caching.
 *
 * @group navigation
 */
class NavigationBlockCacheTest extends BrowserTestBase {

  use NavigationBlockCreationTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['navigation', 'navigation_test', 'test_page_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * User with permission to administer navigation blocks and access navigation.
   *
   * @var object
   */
  protected $adminUser;

  /**
   * An authenticated user to test navigation block caching.
   *
   * @var object
   */
  protected $normalUser;

  /**
   * Another authenticated user to test navigation block caching.
   *
   * @var object
   */
  protected $normalUserAlt;

  /**
   * The navigation block used by this test.
   *
   * @var \Drupal\navigation\NavigationBlockInterface
   */
  protected $navigationBlock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create an admin user, log in and enable test navigation blocks.
    $this->adminUser = $this->drupalCreateUser([
      'administer navigation_block',
      'access administration pages',
      'access navigation',
    ]);
    $this->drupalLogin($this->adminUser);

    // Create additional users to test caching modes.
    $this->normalUser = $this->drupalCreateUser([
      'access navigation',
    ]);
    $this->normalUserAlt = $this->drupalCreateUser();
    // Sync the roles, since drupalCreateUser() creates separate roles for
    // the same permission sets.
    $this->normalUserAlt->roles = $this->normalUser->getRoles();
    $this->normalUserAlt->save();

    // Enable our test navigation block.
    $this->navigationBlock = $this->drupalPlaceNavigationBlock('test_cache');
  }

  /**
   * Tests "user.roles" cache context.
   */
  public function testCachePerRole() {
    \Drupal::state()->set('navigation_test.cache_contexts', ['user.roles']);

    // Enable our test navigation block. Set some content for it to display.
    $current_content = $this->randomMachineName();
    \Drupal::state()->set('navigation_test.content', $current_content);
    $this->drupalLogin($this->normalUser);
    $this->drupalGet('');
    $this->assertSession()->pageTextContains($current_content);

    // Change the content, but the cached copy should still be served.
    $old_content = $current_content;
    $current_content = $this->randomMachineName();
    \Drupal::state()->set('navigation_test.content', $current_content);
    $this->drupalGet('');
    $this->assertSession()->pageTextContains($old_content);

    // Clear the cache and verify that the stale data is no longer there.
    Cache::invalidateTags(['navigation_block_view']);
    $this->drupalGet('');
    $this->assertSession()->pageTextNotContains($old_content);
    // Fresh block content is displayed after clearing the cache.
    $this->assertSession()->pageTextContains($current_content);

    // Test whether the cached data is served for the correct users.
    $old_content = $current_content;
    $current_content = $this->randomMachineName();
    \Drupal::state()->set('navigation_test.content', $current_content);
    $this->drupalLogout();
    $this->drupalGet('');
    // Anonymous user does not see content cached per-role for normal user.
    $this->assertSession()->pageTextNotContains($old_content);

    // User with the same roles sees per-role cached content.
    $this->drupalLogin($this->normalUserAlt);
    $this->drupalGet('');
    $this->assertSession()->pageTextContains($old_content);

    // Admin user does not see content cached per-role for normal user.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('');
    $this->assertSession()->pageTextNotContains($old_content);

    // Navigation block is served from the per-role cache.
    $this->drupalLogin($this->normalUser);
    $this->drupalGet('');
    $this->assertSession()->pageTextContains($old_content);
  }

  /**
   * Tests a cacheable navigation block without any additional cache context.
   */
  public function testCachePermissions() {
    // user.permissions is a required context, so a user with different
    // permissions will see a different version of the navigation block.
    \Drupal::state()->set('navigation_test.cache_contexts', []);

    $current_content = $this->randomMachineName();
    \Drupal::state()->set('navigation_test.content', $current_content);

    $this->drupalGet('');
    $this->assertSession()->pageTextContains($current_content);

    $old_content = $current_content;
    $current_content = $this->randomMachineName();
    \Drupal::state()->set('navigation_test.content', $current_content);

    // Navigation block content served from cache.
    $this->drupalGet('user');
    $this->assertSession()->pageTextContains($old_content);

    // Navigation block content not served from cache.
    $this->drupalLogin($this->normalUser);
    $this->drupalGet('user');
    $this->assertSession()->pageTextContains($current_content);
  }

  /**
   * Tests non-cacheable navigation block.
   */
  public function testNoCache() {
    \Drupal::state()->set('navigation_test.cache_max_age', 0);

    $current_content = $this->randomMachineName();
    \Drupal::state()->set('navigation_test.content', $current_content);

    // If max_age = 0 has no effect, the next request would be cached.
    $this->drupalGet('');
    $this->assertSession()->pageTextContains($current_content);

    // A cached copy should not be served.
    $current_content = $this->randomMachineName();
    \Drupal::state()->set('navigation_test.content', $current_content);
    $this->drupalGet('');
    // Maximum age of zero prevents navigation blocks from being cached.
    $this->assertSession()->pageTextContains($current_content);
  }

  /**
   * Tests "user" cache context.
   */
  public function testCachePerUser() {
    \Drupal::state()->set('navigation_test.cache_contexts', ['user']);

    $current_content = $this->randomMachineName();
    \Drupal::state()->set('navigation_test.content', $current_content);
    $this->drupalLogin($this->normalUser);

    $this->drupalGet('');
    $this->assertSession()->pageTextContains($current_content);

    $old_content = $current_content;
    $current_content = $this->randomMachineName();
    \Drupal::state()->set('navigation_test.content', $current_content);

    // Navigation block is served from per-user cache.
    $this->drupalGet('');
    $this->assertSession()->pageTextContains($old_content);

    // Per-user navigation block cache is not served for other users.
    $this->drupalLogin($this->normalUserAlt);
    $this->drupalGet('');
    $this->assertSession()->pageTextContains($current_content);

    // Per-user navigation block cache is persistent.
    $this->drupalLogin($this->normalUser);
    $this->drupalGet('');
    $this->assertSession()->pageTextContains($old_content);
  }

  /**
   * Tests "url" cache context.
   */
  public function testCachePerPage() {
    \Drupal::state()->set('navigation_test.cache_contexts', ['url']);

    $current_content = $this->randomMachineName();
    \Drupal::state()->set('navigation_test.content', $current_content);

    $this->drupalGet('test-page');
    $this->assertSession()->pageTextContains($current_content);

    $old_content = $current_content;
    $current_content = $this->randomMachineName();
    \Drupal::state()->set('navigation_test.content', $current_content);

    $this->drupalGet('user');
    $this->assertSession()->statusCodeEquals(200);
    // Verify that navigation block content cached for the test page does not
    // show up for the user page.
    $this->assertSession()->pageTextNotContains($old_content);
    $this->drupalGet('test-page');
    $this->assertSession()->statusCodeEquals(200);
    // Verify that the navigation block content is cached for the test page.
    $this->assertSession()->pageTextContains($old_content);
  }

}
