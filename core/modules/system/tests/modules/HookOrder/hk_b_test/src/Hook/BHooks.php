<?php

declare(strict_types=1);

namespace Drupal\hk_b_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hooks for testing ordering.
 */
class BHooks {

  #[Hook('test_hook')]
  public function testHook(): string {
    return __METHOD__;
  }

  #[Hook('sparse_test_hook')]
  public function sparseTestHook(): string {
    return __METHOD__;
  }

}
