<?php

namespace Drupal\KernelTests\Core\Theme;

use Drupal\Core\Cache\MemoryBackendFactory;
use Drupal\Core\Cache\NullBackendFactory;
use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Tests theme_debug functionality.
 *
 * @group Theme
 */
class ThemeDebugTest extends KernelTestBase {

  public function testPageCacheBackend(): void {
    $this->assertCacheBinFactory('page', MemoryBackendFactory::class);
    $this->toggleThemeDebug();
    $this->assertCacheBinFactory('page', NullBackendFactory::class);
    $this->toggleThemeDebug();
  }

  public function testDynamicPageCacheBackend(): void {
    $this->assertCacheBinFactory('dynamic_page_cache', MemoryBackendFactory::class);
    $this->toggleThemeDebug();
    $this->assertCacheBinFactory('dynamic_page_cache', NullBackendFactory::class);
    $this->toggleThemeDebug();
  }

  public function testRenderCacheBackend(): void {
    $this->assertCacheBinFactory('render', MemoryBackendFactory::class);
    $this->toggleThemeDebug();
    $this->assertCacheBinFactory('render', NullBackendFactory::class);
    $this->toggleThemeDebug();
  }

  private function toggleThemeDebug(): void {
    $this->setSetting('theme_debug', !Settings::get('theme_debug', FALSE));
    $this->container->get('kernel')->rebuildContainer();
  }

  /**
   * @phpstan-param class-string $expected
   */
  private function assertCacheBinFactory(string $bin, string $expected): void {
    self::assertTrue($this->container->hasDefinition("cache.$bin"));
    $factory = $this->container->getDefinition("cache.$bin")->getFactory();
    self::assertIsArray($factory);
    $reference = $factory[0];
    self::assertInstanceOf(Reference::class, $reference);
    self::assertInstanceOf(
      $expected,
      $this->container->get($reference)
    );
  }

}
