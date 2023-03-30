<?php

declare(strict_types=1);

namespace Drupal\Tests\announcements_feed\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Defines a class for testing pages are still cacheable with dynamic page cache.
 *
 * @group announcements_feed
 */
final class AnnouncementsCacheTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'announcements_feed',
    'dynamic_page_cache',
    'toolbar',
  ];

  /**
   * Tests dynamic page cache.
   */
  public function testDynamicPageCache(): void {
    $this->drupalLogin($this->drupalCreateUser([
      'access toolbar',
      'access announcements',
    ]));
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->elementExists('css', '[data-drupal-announce-trigger]');
    $this->assertNotEquals('UNCACHEABLE', $this->getSession()->getResponseHeader('X-Drupal-Dynamic-Cache'));
  }

}
