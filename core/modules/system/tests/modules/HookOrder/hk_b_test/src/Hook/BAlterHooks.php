<?php

declare(strict_types=1);

namespace Drupal\hk_b_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hooks for testing ordering.
 */
class BAlterHooks {

  #[Hook('test_subtype_alter')]
  public function testSubtypeAlter(array &$calls): void {
    $calls[] = __METHOD__;
  }

}
