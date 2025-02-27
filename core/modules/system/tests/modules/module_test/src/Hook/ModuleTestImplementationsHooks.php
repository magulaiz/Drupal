<?php

namespace Drupal\module_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for module_test.
 */
class ModuleTestImplementationsHooks {
  /**
   * Implements hook_altered_test_hook().
   *
   * @see module_test_module_implements_alter()
   */
  #[Hook('altered_test_hook')]
  public function alteredTestHook(): string {
    return 'module_test_altered_test_hook';
  }
}
