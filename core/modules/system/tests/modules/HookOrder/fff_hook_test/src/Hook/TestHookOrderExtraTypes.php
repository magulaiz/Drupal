<?php

declare(strict_types=1);

namespace Drupal\fff_hook_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Order\OrderAfter;

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
class TestHookOrderExtraTypes {

  /**
   * This pair tests OrderAfter with ExtraTypes.
   */
  #[Hook('custom_hook_extra_types1_alter',
    order: new OrderAfter(
      modules: ['ggg_hook_test'],
    )
  )]
  public function customHookExtraTypes(array &$calls): void {
    // This should be run after.
    $calls[] = __METHOD__;
  }

}
