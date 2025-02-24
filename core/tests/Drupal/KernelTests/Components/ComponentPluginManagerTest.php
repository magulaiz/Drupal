<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Components;

use Drupal\Core\Render\Component\Exception\ComponentNotFoundException;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Tests the component plugin manager.
 *
 * @group sdc
 */
class ComponentPluginManagerTest extends ComponentKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'sdc_test', 'sdc_test_replacements'];

  /**
   * {@inheritdoc}
   */
  protected static $themes = ['sdc_theme_test'];

  /**
   * Test that components render correctly.
   */
  public function testFindEmptyMetadataFile(): void {
    // Test that empty component metadata files are valid, since there is no
    // required property.
    $this->assertNotEmpty(
      $this->manager->find('sdc_theme_test:bar'),
    );
    // Test that if the folder name does not match the machine name, the
    // component is still available.
    $this->assertNotEmpty(
      $this->manager->find('sdc_theme_test:foo'),
    );
  }

  /**
   * Test that the machine name is grabbed from the *.component.yml.
   *
   * And not from the enclosing directory.
   */
  public function testMismatchingFolderName(): void {
    $this->expectException(ComponentNotFoundException::class);
    $this->manager->find('sdc_theme_test:mismatching-folder-name');
  }

  /**
   * Test component definitions caching depending on twig debug/cache settings.
   *
   * @dataProvider providerTestComponentCachingDependingOnDevelopmentSettings
   */
  public function testComponentCachingDependingOnDevelopmentSettings(bool $twigDebug, bool $cacheEnabled, int $expectedCacheGets): void {
    // Set the development settings.
    $developmentSettings = $this->keyValue->get('development_settings');
    $developmentSettings->set('twig_debug', $twigDebug);
    $developmentSettings->set('twig_cache_disable', !$cacheEnabled);

    // Set the cache backend as a spy mock.
    $cacheBackend = $this->createMock(CacheBackendInterface::class);
    $cacheBackend->expects($this->exactly($expectedCacheGets))->method('get')->with('cache_key');
    $this->manager->setCacheBackend($cacheBackend, 'cache_key');

    // Make two calls to getDefinitions() to ensure cache is called if needed.
    $this->manager->getDefinitions();
    $this->manager->getDefinitions();
  }

  /**
   * Data provider for testComponentCachingDependingOnDevelopmentSettings().
   */
  public static function providerTestComponentCachingDependingOnDevelopmentSettings(): array {
    return [
      'Debug enabled, cache enabled' => [TRUE, TRUE, 0],
      'Debug enabled, cache disabled' => [TRUE, FALSE, 0],
      'Debug disabled, cache enabled' => [FALSE, TRUE, 1],
      'Debug disabled, cache disabled' => [FALSE, FALSE, 0],
    ];
  }

}
