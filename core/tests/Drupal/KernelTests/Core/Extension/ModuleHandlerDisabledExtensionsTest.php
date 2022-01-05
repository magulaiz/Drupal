<?php

namespace Drupal\KernelTests\Core\Extension;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests ModuleHandler with disabled extensions.
 *
 * @group ModuleHandler
 */
class ModuleHandlerDisabledExtensionsTest extends KernelTestBase {

  /**
   * Test inclusion of the files for disabled extensions.
   */
  public function testLoadIncludeWithDisabledModule() {
    /** @var \Drupal\Core\Extension\ModuleHandler $module_handler */
    $module_handler = \Drupal::service('module_handler');
    // Module extension.
    $this->assertFalse(function_exists('module_test_system_info_alter'), 'Test function already defined during runtime');
    $module_handler->loadInclude('module_test', 'module');
    $this->assertFalse(function_exists('module_test_system_info_alter'), 'Module handler able to include file of uninstalled extensions');
    $this->assertFalse(function_exists('module_test_system_info_alter'), 'Test function already defined during runtime');
    $module_handler->loadInclude('module_test', 'module', NULL, TRUE);
    $this->assertTrue(function_exists('module_test_system_info_alter'), 'Module handler unable to include file of uninstalled extensions');
    // Engine extension.
    $this->assertFalse(function_exists('nyan_cat_extension'), 'Test function already defined during runtime');
    $module_handler->loadInclude('nyan_cat', 'engine');
    $this->assertFalse(function_exists('nyan_cat_extension'), 'Module handler able to include file of uninstalled extensions');
    // Theme extension.
    $this->assertFalse(function_exists('test_theme_library_info_alter'), 'Test function already defined during runtime');
    $module_handler->loadInclude('test_theme', 'theme');
    $this->assertFalse(function_exists('test_theme_library_info_alter'), 'Module handler able to include file of uninstalled extensions');
    // Profile extension.
    $this->assertFalse(function_exists('testing_requirements_requirements'), 'Test function already defined during runtime');
    $module_handler->loadInclude('testing_requirements', 'install');
    $this->assertFalse(function_exists('testing_requirements_requirements'), "Module handler able to include file of uninstalled extensions");
  }

}
