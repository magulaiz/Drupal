<?php

declare(strict_types=1);

namespace Drupal\module_test\Hook;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Hook\Attribute\Hook;

class ModuleTestHooks {

  /**
   * Implements hook_system_info_alter().
   *
   * Manipulate module dependencies to test dependency chains.
   */
  #[Hook('system_info_alter')]
  public function systemInfoAlter(&$info, Extension $file, $type) {
    if (\Drupal::state()->get('module_test.dependency') == 'missing dependency') {
      if ($file->getName() == 'dblog') {
        // Make dblog module depend on config.
        $info['dependencies'][] = 'config';
      }
      elseif ($file->getName() == 'config') {
        // Make config module depend on a non-existing module.
        $info['dependencies'][] = 'foo';
      }
    }
    elseif (\Drupal::state()->get('module_test.dependency') == 'dependency') {
      if ($file->getName() == 'dblog') {
        // Make dblog module depend on config.
        $info['dependencies'][] = 'config';
      }
      elseif ($file->getName() == 'config') {
        // Make config module depend on help module.
        $info['dependencies'][] = 'help';
      }
      elseif ($file->getName() == 'entity_test') {
        // Make entity test module depend on help module.
        $info['dependencies'][] = 'help';
      }
    }
    elseif (\Drupal::state()->get('module_test.dependency') == 'version dependency') {
      if ($file->getName() == 'dblog') {
        // Make dblog module depend on config.
        $info['dependencies'][] = 'config';
      }
      elseif ($file->getName() == 'config') {
        // Make config module depend on a specific version of help module.
        $info['dependencies'][] = 'help (1.x)';
      }
      elseif ($file->getName() == 'help') {
        // Set help module to a version compatible with the above.
        $info['version'] = '8.x-1.0';
      }
    }
    if ($file->getName() == 'stark' && $type == 'theme') {
      $info['regions']['test_region'] = 'Test region';
    }
  }

  /**
   * Implements hook_hook_info().
   */
  #[Hook('hook_info')]
  public function hookInfo() {
    $hooks['test_hook'] = ['group' => 'file'];
    return $hooks;
  }

  /**
   * Implements hook_module_implements_alter().
   *
   * @see module_test_altered_test_hook()
   * @see \Drupal\system\Tests\Module\ModuleImplementsAlterTest::testModuleImplementsAlter()
   */
  #[Hook('module_implements_alter')]
  public function moduleImplementsAlter(&$implementations, $hook) {
    if ($hook === 'altered_test_hook') {
      // Add a hook implementation, that will be found in
      // module_test.implementation.inc.
      $implementations['module_test'] = 'implementations';
    }
    if ($hook === 'unimplemented_test_hook') {
      // Add the non-existing function module_test_unimplemented_test_hook(). This
      // should cause an exception to be thrown in
      // \Drupal\Core\Extension\ModuleHandler::buildImplementationInfo('unimplemented_test_hook').
      $implementations['module_test'] = \FALSE;
    }
  }

}
