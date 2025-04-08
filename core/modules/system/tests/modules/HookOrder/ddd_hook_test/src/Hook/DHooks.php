<?php

declare(strict_types=1);

namespace Drupal\ddd_hook_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Attribute\RemoveHook;
use Drupal\Core\Hook\Attribute\ReOrderHook;
use Drupal\Core\Hook\Order\Order;
use Drupal\ccc_hook_test\Hook\CHooks;

/**
 * Hooks for testing ordering.
 */
#[ReOrderHook('test_hook', CHooks::class, 'testHookReOrderFirst', Order::First)]
#[RemoveHook('test_hook', CHooks::class, 'testHookRemoved')]
class DHooks {

  #[Hook('test_hook')]
  public function testHook(): string {
    return __METHOD__;
  }

  #[Hook('sparse_test_hook')]
  public function sparseTestHook(): string {
    return __METHOD__;
  }

}
