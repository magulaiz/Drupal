<?php

declare(strict_types=1);

namespace Drupal\FunctionalTests\Routing;

use Drupal\Tests\BrowserTestBase;

/**
 * @group routing
 */
class DefaultFormatTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'default_format_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Set some page cache max-age so that responses are cacheable.
    $this->config('system.performance')
      ->set('cache.page.max_age', 300)
      ->save();
  }

  public function testFoo(): void {
    $this->drupalGet('/default_format_test/human');
    $this->assertSame('format:html', $this->getSession()->getPage()->getContent());
    $this->assertSession()->responseHeaderEquals('X-Drupal-Cache', 'MISS');
    $this->drupalGet('/default_format_test/human');
    $this->assertSame('format:html', $this->getSession()->getPage()->getContent());
    $this->assertSession()->responseHeaderEquals('X-Drupal-Cache', 'HIT');

    $this->drupalGet('/default_format_test/machine');
    $this->assertSame('format:json', $this->getSession()->getPage()->getContent());
    $this->assertSession()->responseHeaderEquals('X-Drupal-Cache', 'MISS');
    $this->drupalGet('/default_format_test/machine');
    $this->assertSame('format:json', $this->getSession()->getPage()->getContent());
    $this->assertSession()->responseHeaderEquals('X-Drupal-Cache', 'HIT');
  }

  public function testMultipleRoutesWithSameSingleFormat(): void {
    $this->drupalGet('/default_format_test/machine');
    $this->assertSame('format:json', $this->getSession()->getPage()->getContent());
  }

}
