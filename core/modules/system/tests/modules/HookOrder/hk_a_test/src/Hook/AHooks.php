<?php

declare(strict_types=1);

namespace Drupal\hk_a_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Order;
use Drupal\Core\Hook\OrderAfter;

/**
 * Hooks for testing ordering.
 */
class AHooks {

  #[Hook('test_hook')]
  public function testHook(): string {
    return __METHOD__;
  }

  #[Hook('test_hook', order: Order::First)]
  public function testHookFirst(): string {
    return __METHOD__;
  }

  #[Hook('test_hook', order: Order::Last)]
  public function testHookLast(): string {
    return __METHOD__;
  }

  #[Hook('test_hook', order: new OrderAfter(modules: ['hk_b_test']))]
  public function testHookAfterB(): string {
    return __METHOD__;
  }

}
