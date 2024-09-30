<?php

namespace Drupal\Tests\Core\Deprecation;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the deprecation of functions.
 *
 * @group core
 * @group legacy
 */
class InstallVersionInfoDeprecationTest extends KernelTestBase {

  /**
   * Tests that _install_get_version_info() triggers a deprecation notice.
   */
  public function testInstallGetVersionInfoDeprecation():void {

    include_once $this->root . '/core/includes/install.core.inc';
    $version = '8.0.0-alpha2';
    // Expect a deprecation warning.
    $this->expectDeprecation('The _install_get_version_info() function is deprecated in drupal:11.0.0 and is removed from drupal:12.0.0. Use explode() instead. See https://www.drupal.org/node/3476950');

    // Call the deprecated function to trigger the notice.
    _install_get_version_info($version);
  }

}
