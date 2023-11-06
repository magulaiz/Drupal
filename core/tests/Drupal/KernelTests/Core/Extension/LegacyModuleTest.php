<?php

namespace Drupal\KernelTests\Core\Extension;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests legacy module function deprecations.
 *
 * @group legacy
 * @group extension
 */
class LegacyModuleTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['module_test'];

  /**
   * Tests module_set_weight() deprecation.
   */
  public function testModuleSetWeightDeprecation(): void {
    $this->expectDeprecation('module_set_weight() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Instead set \'core.extension\' config directly. See https://www.drupal.org/project/drupal/issues/3359527');
    module_set_weight('module_test', 100);
  }

}
