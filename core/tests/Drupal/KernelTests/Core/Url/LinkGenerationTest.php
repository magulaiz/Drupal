<?php

namespace Drupal\KernelTests\Core\Url;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Url;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests link generation with hooks.
 *
 * @group Utility
 */
class LinkGenerationTest extends KernelTestBase {

  protected static $modules = ['link_generation_test'];

  /**
   * Tests how hook_link_alter() can affect escaping of the link text.
   */
  public function testHookLinkAlter() {
    $url = Url::fromUri('http://example.com');
    $renderer = \Drupal::service('renderer');

    $link = $renderer->executeInRenderContext(new RenderContext(), function () use ($url) {
      return \Drupal::service('link_generator')->generate(['#markup' => '<em>link with markup</em>'], $url);
    });
    $this->setRawContent($link);
    $this->assertInstanceOf(MarkupInterface::class, $link);
    // Ensure the content of the link is not escaped.
    $this->assertRaw('<em>link with markup</em>');

    // Check cacheable metadata with no hook_link_alter() alteration.
    // @see link_generation_test_link_alter()
    $this->assertInstanceOf(RefinableCacheableDependencyInterface::class, $link);
    $this->assertEmpty($link->getCacheContexts());
    $this->assertEmpty($link->getCacheTags());
    $this->assertSame(Cache::PERMANENT, $link->getCacheMaxAge());

    // Test just adding text to an already safe string.
    \Drupal::state()->set('link_generation_test_link_alter', TRUE);
    $link = $renderer->executeInRenderContext(new RenderContext(), function () use ($url) {
      return \Drupal::service('link_generator')->generate(['#markup' => '<em>link with markup</em>'], $url);
    });
    $this->setRawContent($link);
    $this->assertInstanceOf(MarkupInterface::class, $link);
    // Ensure the content of the link is escaped.
    $this->assertEscaped('<em>link with markup</em> <strong>Test!</strong>');

    // Check that cacheable metadata has been altered by hook_link_alter().
    // @see link_generation_test_link_alter()
    $this->assertInstanceOf(RefinableCacheableDependencyInterface::class, $link);
    $this->assertSame(['languages', 'url'], $link->getCacheContexts());
    $this->assertSame(['bar', 'foo'], $link->getCacheTags());
    $this->assertSame(3600, $link->getCacheMaxAge());

    // Test passing a safe string to t().
    \Drupal::state()->set('link_generation_test_link_alter_safe', TRUE);
    $link = $renderer->executeInRenderContext(new RenderContext(), function () use ($url) {
      return \Drupal::service('link_generator')->generate(['#markup' => '<em>link with markup</em>'], $url);
    });
    $this->setRawContent($link);
    $this->assertInstanceOf(MarkupInterface::class, $link);
    // Ensure the content of the link is escaped.
    $this->assertRaw('<em>link with markup</em> <strong>Test!</strong>');

    // Test passing an unsafe string to t().
    $link = $renderer->executeInRenderContext(new RenderContext(), function () use ($url) {
      return \Drupal::service('link_generator')->generate('<em>link with markup</em>', $url);
    });
    $this->setRawContent($link);
    $this->assertInstanceOf(MarkupInterface::class, $link);
    // Ensure the content of the link is escaped.
    $this->assertEscaped('<em>link with markup</em>');
    $this->assertRaw('<strong>Test!</strong>');
  }

}
