<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Functional\Form;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the performance settings form.
 *
 * @group Form
 */
class PerformanceFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Create an admin user.
    $admin_user = $this->drupalCreateUser([
      'administer site configuration',
    ]);
    $this->drupalLogin($admin_user);
  }

  /**
   * Tests that we can set a page cache maximum age.
   */
  public function testPageCacheMaximumAge(): void {
    $this->drupalGet('admin/config/development/performance');
    $this->assertSession()
      ->elementAttributeContains('css', '#edit-page-cache-maximum-age', 'value', '0');

    // Try to change the value.
    $this->submitForm(['page_cache_maximum_age' => 42], 'Save configuration');
    $this->assertSession()
      ->elementAttributeContains('css', '#edit-page-cache-maximum-age', 'value', '42');
    $this->submitForm(['page_cache_maximum_age' => 31536000], 'Save configuration');
    $this->assertSession()
      ->elementAttributeContains('css', '#edit-page-cache-maximum-age', 'value', '31536000');
  }

}
