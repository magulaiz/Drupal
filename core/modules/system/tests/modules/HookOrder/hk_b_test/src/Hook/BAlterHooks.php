<?php

declare(strict_types=1);

namespace Drupal\hk_b_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\OrderAfter;

/**
 * Hooks for testing ordering.
 */
class BAlterHooks {

  #[Hook('test_alter', order: new OrderAfter(modules: ['hk_c_test']))]
  public function testAlterAfterCExtra(array &$calls): void {
    $calls[] = __METHOD__;
  }

  #[Hook('test_subtype_alter')]
  public function testSubtypeAlter(array &$calls): void {
    $calls[] = __METHOD__;
  }

}
