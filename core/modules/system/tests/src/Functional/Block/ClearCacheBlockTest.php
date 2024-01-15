<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Functional\Block;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests clear cache block behavior.
 *
 * @group Block
 *
 * @see \Drupal\system\Plugin\Block\ClearCacheBlock
 */
class ClearCacheBlockTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'system_block_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $admin_user = $this->drupalCreateUser(['administer site configuration']);
    $this->drupalLogin($admin_user);
  }

  /**
   * Tests block access based on permissions.
   */
  public function testCacheClearBlockAccess() {
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Clear cache block');
    $this->drupalLogout();
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextNotContains('Clear cache block');
  }

  /**
   * Tests block behavior.
   */
  public function testCacheClearBlock() {
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Clear cache block');
    $page = $this->getSession()->getPage();
    $page->pressButton('Clear all caches');
    $this->assertSession()->statusMessageContains('Caches cleared.');
  }

}
