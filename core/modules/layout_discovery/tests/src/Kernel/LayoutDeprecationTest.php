<?php

namespace Drupal\Tests\layout_discovery\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests Layout functionality.
 *
 * @group Layout
 * @group legacy
 */
class LayoutDeprecationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'layout_discovery',
    'layout_deprecation_test',
  ];

  /**
   * Test deprecation of plugin defined by yaml.
   */
  public function testLayoutDeprecation() {
    $this->expectDeprecation('This layout is now deprecated. Bye!');
    $this->container->get('plugin.manager.core.layout')
      ->createInstance('layout_deprecation_test', []);
  }

  /**
   * Test deprecation of plugin defined by annotations.
   */
  public function testLayoutDeprecated() {
    $this->expectDeprecation('layout_deprecated is deprecated');
    $this->container->get('plugin.manager.core.layout')
      ->createInstance('layout_deprecated', []);
  }

}
