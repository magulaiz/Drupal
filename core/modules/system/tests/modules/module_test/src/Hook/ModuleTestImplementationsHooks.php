<?php

declare(strict_types=1);

namespace Drupal\module_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;

class ModuleTestImplementationsHooks {

  /**
   * Implements hook_altered_test_hook().
   *
   * @see module_test_module_implements_alter()
   */
  #[Hook('altered_test_hook')]
    public function alteredTestHook() {
    return __FUNCTION__;
    }

}
