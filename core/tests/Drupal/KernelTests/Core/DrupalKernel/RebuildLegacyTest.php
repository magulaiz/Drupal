<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\DrupalKernel;

use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests utility.inc functions.
 *
 * @group legacy
 */
class RebuildLegacyTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    include_once $this->root . '/core/includes/utility.inc';
  }

  /**
   * Tests drupal_rebuild().
   */
  public function testDrupalRebuild(): void {
    $this->expectDeprecation('drupal_rebuild() is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use rebuild.php script instead. See https://www.drupal.org/node/3014783');
    drupal_rebuild($this->classLoader, Request::createFromGlobals());
  }

  /**
   * Tests drupal_flush_all_caches().
   */
  public function testDrupalFlushAllCaches(): void {
    $this->expectDeprecation('drupal_flush_all_caches() is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use \Drupal\Core\Cache\Rebuilder::rebuildAll() instead. See https://www.drupal.org/node/3014783');
    drupal_flush_all_caches();
  }

}
