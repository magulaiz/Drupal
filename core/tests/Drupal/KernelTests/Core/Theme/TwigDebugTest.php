<?php

namespace Drupal\KernelTests\Core\Theme;

use Drupal\Core\Cache\MemoryBackendFactory;
use Drupal\Core\Cache\NullBackendFactory;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests twig_debug functionality.
 *
 * @group Theme
 */
class TwigDebugTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);
    $container->register('cache.dynamic_page_cache', 'Drupal\Core\Cache\MemoryBackendFactory')
      ->addTag('persist');
    $container->register('cache.page', 'Drupal\Core\Cache\MemoryBackendFactory')
      ->addTag('persist');
    $container->register('cache.render', 'Drupal\Core\Cache\MemoryBackendFactory')
      ->addTag('persist');
  }

  public function testCacheBackend(): void {
    $this->assertCacheBinFactory('dynamic_page_cache', MemoryBackendFactory::class);
    $this->assertCacheBinFactory('page', MemoryBackendFactory::class);
    $this->assertCacheBinFactory('render', MemoryBackendFactory::class);
    $this->enableTwigDebug();
    $this->assertCacheBinFactory('dynamic_page_cache', NullBackendFactory::class);
    $this->assertCacheBinFactory('page', NullBackendFactory::class);
    $this->assertCacheBinFactory('render', NullBackendFactory::class);
  }

  private function enableTwigDebug(): void {
    $this->setSetting('twig_debug', TRUE);
    $this->setSetting('twig_cache_disable', TRUE);
    $this->container->get('kernel')->rebuildContainer();
  }

  /**
   * @phpstan-param class-string $expected
   */
  private function assertCacheBinFactory(string $bin, string $expected): void {
    self::assertTrue($this->container->hasDefinition("cache.$bin"));
    $class = $this->container->getDefinition("cache.$bin")->getClass();
    self::assertEquals($expected, $class);
  }

}
