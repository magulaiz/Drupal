<?php

declare(strict_types=1);

namespace Drupal\Tests\block\Functional;

use Drupal\Core\Cache\Cache;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests block caching.
 *
 * @group block
 */
class BlockCacheTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block', 'block_test', 'test_page_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A user with permission to administer blocks.
   *
   * @var object
   */
  protected $adminUser;

  /**
   * An authenticated user to test block caching.
   *
   * @var object
   */
  protected $normalUser;

  /**
   * Another authenticated user to test block caching.
   *
   * @var object
   */
  protected $normalUserAlt;

  /**
   * The block used by this test.
   *
   * @var \Drupal\block\BlockInterface
   */
  protected $block;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create an admin user, log in and enable test blocks.
    $this->adminUser = $this->drupalCreateUser([
      'administer blocks',
      'access administration pages',
    ]);
    $this->drupalLogin($this->adminUser);

    // Create additional users to test caching modes.
    $this->normalUser = $this->drupalCreateUser();
    $this->normalUserAlt = $this->drupalCreateUser();
    // Sync the roles, since drupalCreateUser() creates separate roles for
    // the same permission sets.
    $this->normalUserAlt->roles = $this->normalUser->getRoles();
    $this->normalUserAlt->save();

    // Enable our test block.
    $this->block = $this->drupalPlaceBlock('test_cache');
  }

  /**
   * Tests a cacheable block without any additional cache context.
   */
  public function testCachePermissions(): void {
    $current_content = $this->randomMachineName();
    \Drupal::state()->set('block_test.content', $current_content);

    $this->drupalGet('');
    $this->assertSession()->pageTextContains($current_content);

    $old_content = $current_content;
    $current_content = $this->randomMachineName();
    \Drupal::state()->set('block_test.content', $current_content);
    $this->assertEquals($current_content, \Drupal::state()->get('block_test.content'));

    // Block content served from cache.
    $this->drupalGet('user/' . $this->adminUser->id());
    $this->assertEquals($current_content, \Drupal::state()->get('block_test.content'));
    $this->assertSession()->pageTextContains($old_content);
    $this->assertEquals($current_content, \Drupal::state()->get('block_test.content'));

    // Block content not served from cache.
    $this->drupalLogout();
    $this->assertEquals($current_content, \Drupal::state()->get('block_test.content'));
    $this->assertSession()->pageTextContains($current_content);
  }

}
