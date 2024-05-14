<?php

namespace Drupal\KernelTests\Core\Cache;

use Drupal\KernelTests\KernelTestBase;

/**
 * Unit test of the database backend using the generic cache unit test base.
 *
 * @group Cache
 */
class DelegatedCacheFactoryTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['system'];

  /**
   * Test deprecated service without bin tag.
   * @group legacy
   */
  public function testSetGet() {
    $this->expectDeprecation('Service "module_cache_bin_delegated.deprecated_missing_bin_tag" omits the bin tag from the service definition which is deprecated in drupal:11.0.0 and support will be removed in drupal:12.0.0. See https://www.drupal.org/project/drupal/issues/3272093');
    $this->enableModules(['module_cache_bin_delegated']);
    \Drupal::cache('deprecated_missing_bin_tag');
  }

}
