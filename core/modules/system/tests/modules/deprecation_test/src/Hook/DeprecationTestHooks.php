<?php

declare(strict_types=1);

namespace Drupal\deprecation_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;

class DeprecationTestHooks {

  /**
   * Implements hook_deprecated_hook().
   */
  #[Hook('deprecated_hook')]
  public function deprecatedHook($arg) {
    return $arg;
  }

  /**
   * Implements hook_deprecated_alter_alter().
   */
  #[Hook('deprecated_alter_alter')]
  public function deprecatedAlterAlter(&$data, $context1, $context2) {
    $data = [$context1, $context2];
  }

}
