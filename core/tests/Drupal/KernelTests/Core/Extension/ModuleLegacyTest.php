<?php

namespace Drupal\KernelTests\Core\Extension;

use Drupal\Component\Utility\SortArray;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests deprecations from module.inc file.
 *
 * @group legacy
 */
class ModuleLegacyTest extends KernelTestBase {

  /**
   * Test deprecation of module_load_include() function.
   */
  public function testModuleLoadInclude() {
    $this->assertFalse($this->container->get('module_handler')->moduleExists('module_test'), 'Ensure module is uninstalled so we test the ability to include uninstalled code.');
    $this->expectDeprecation('module_load_include() is deprecated in drupal:9.4.0 and is removed from drupal:11.0.0. Instead, you should use \Drupal::moduleHandler()->loadInclude(). Note that including code from uninstalled extensions is no longer supported. See https://www.drupal.org/node/2948698');
    $filename = module_load_include('inc', 'module_test', 'module_test.file');
    $this->assertStringEndsWith("module_test.file.inc", $filename);

  }

  /**
   * Tests the deprecation of module_config_sort().
   */
  public function testModuleConfigSort(): void {
    $modules = $this->config('core.extension')->get('module');
    $modules['module_test'] = 0;
    $this->expectDeprecation('module_config_sort() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use \Drupal\Component\Utility\SortArray::sortByNumericValueAndKey() instead. See https://www.drupal.org/node/3262814');
    $sorted_modules_module_config_sort = module_config_sort($modules);
    SortArray::sortByNumericValueAndKey($modules);
    $this->assertEquals($modules, $sorted_modules_module_config_sort);
  }

}
