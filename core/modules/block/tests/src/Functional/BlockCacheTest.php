<?php

declare(strict_types=1);

namespace Drupal\Tests\block\Functional;

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
   * Tests a cacheable block without any additional cache context.
   */
  public function testCachePermissions(): void {
    $adminUser = $this->drupalCreateUser([
      'administer blocks',
      'access administration pages',
    ]);
    $current_content = $this->randomMachineName();
    \Drupal::state()->set('block_test.content', $current_content);

    $this->drupalPlaceBlock('test_cache');

    $this->drupalLogin($adminUser);

    $this->assertSession()->pageTextContains($current_content);

    $old_content = $current_content;
    $current_content = $this->randomMachineName();
    \Drupal::state()->set('block_test.content', $current_content);

    // Block content served from cache.
    $this->drupalGet('user');
    $this->assertSession()->pageTextContains($old_content);

    // Block content not served from cache.
    $this->drupalLogout();
    $this->assertSession()->pageTextContains($current_content);
  }

}
