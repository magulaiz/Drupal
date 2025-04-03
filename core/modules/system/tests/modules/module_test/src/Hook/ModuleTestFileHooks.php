<?php

namespace Drupal\module_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for module_test.
 */
class ModuleTestFileHooks {
  /**
   * Implements hook_test_hook().
   */
  #[Hook('test_hook')]
  public function testHook(): array {
    return [
      'module_test' => 'success!',
    ];
  }
}
