<?php

namespace Drupal\Tests\update\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the module_installer service.
 *
 * @group update
 */
class ModuleInstallerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'update',
  ];

  /**
   * Tests the module_installer service.
   */
  public function testModuleInstaller() {
    $keyvalue = $this->container->get('keyvalue.expirable')->get('update');
    $keyvalue->set('key', 'some value');
    $this->container->get('module_installer')->install(['help']);
    $this->assertNull($keyvalue->get('key'));
  }

}
