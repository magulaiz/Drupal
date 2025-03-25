<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Cache;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the ChainCacheClearer.
 *
 * @group Cache
 * @coversDefaultClass \Drupal\Core\Cache\CacheClearer
 */
class ChainCacheClearerTest extends KernelTestBase {

  /**
   * @covers ::clearCache
   */
  public function testCacheClear(): void {
    /** @var \Drupal\Core\Cache\CacheClearer $chainClearer */
    $chainClearer = $this->container->get('cache_clearer');
    $this->assertNotNull($chainClearer);
    $chainClearer->clearCache();
  }

}
