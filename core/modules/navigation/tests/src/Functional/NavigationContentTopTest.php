<?php

declare(strict_types=1);

namespace Drupal\Tests\navigation\Functional;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

// cspell:ignore foobarbaz baznew

/**
 * Tests for navigation content_top section.
 *
 * @group navigation
 */
class NavigationContentTopTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['navigation', 'navigation_test', 'test_page_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalLogin($this->createUser([
      'access navigation',
    ]));
  }

  /**
   * Tests behavior of content_top section hooks.
   *
   * @dataProvider provideHookContentSectionsData
   */
  public function testNavigationContentTop($hook, $selector, $content, $content_changed): void {
    $test_page_url = Url::fromRoute('test_page_test.test_page');
    $this->drupalGet($test_page_url);
    $this->assertSession()->elementNotExists('css', $selector);
    \Drupal::keyValue('navigation_test')->set($hook, 1);
    Cache::invalidateTags(['navigation_test']);
    $this->drupalGet($test_page_url);
    $this->assertSession()->elementTextContains('css', $selector, $content);
    \Drupal::keyValue('navigation_test')->set($hook . '_alter', 1);
    Cache::invalidateTags(['navigation_test']);
    $this->drupalGet($test_page_url);
    $this->assertSession()->elementTextContains('css', $selector, $content_changed);
  }

  /**
   * Data provider for testNavigationContentTop().
   */
  public static function provideHookContentSectionsData(): array {
    return [
      ['content_top', '.admin-toolbar__content-top', 'foobarbaz', 'baznew bar'],
      ['content_footer_top', '.admin-toolbar__content-footer-top', 'foobarbaz', 'baznew bar'],
    ];
  }

}
