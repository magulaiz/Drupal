<?php

declare(strict_types=1);

namespace Drupal\fff_hook_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for verifying ordering hooks by attributes.
 *
 * We must ensure that the order of the modules is expected and then change
 * the order that the hooks are run in order to verify. This module
 * comes in a pair first alphabetically and last alphabetically.
 *
 * In the normal order a hook implemented by first alphabetically would run
 * before the same hook in last alphabetically.
 *
 * Each method pair tests one hook ordering permutation.
 */
class TestHookFirst {

  /**
   * This pair tests OrderFirst.
   */
  #[Hook('custom_hook_test_hook_first')]
  public function hookFirst(): string {
    // This should be run second, there is another hook reordering before this.
    return __METHOD__;
  }

}
