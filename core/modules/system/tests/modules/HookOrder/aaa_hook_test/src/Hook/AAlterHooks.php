<?php

declare(strict_types=1);

namespace Drupal\aaa_hook_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Order\OrderAfter;

/**
 * Hooks for testing ordering.
 */
class AAlterHooks {

  #[Hook('test_alter', order: new OrderAfter(modules: ['ccc_hook_test']))]
  public function testAlterAfterC(array &$calls): void {
    $calls[] = __METHOD__;
  }

  #[Hook('test_subtype_alter')]
  public function testSubtypeAlter(array &$calls): void {
    $calls[] = __METHOD__;
  }

}
