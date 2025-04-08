<?php

declare(strict_types=1);

namespace Drupal\ccc_hook_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Order\Order;

/**
 * Hooks for testing ordering.
 */
class CHooks {

  #[Hook('test_hook')]
  public function testHook(): string {
    return __METHOD__;
  }

  #[Hook('test_hook', order: Order::First)]
  public function testHookFirst(): string {
    return __METHOD__;
  }

  /**
   * This implementation is reordered from elsewhere.
   *
   * @see \Drupal\ddd_hook_test\Hook\DHooks
   */
  #[Hook('test_hook')]
  public function testHookReOrderFirst(): string {
    return __METHOD__;
  }

  /**
   * This implementation is removed from elsewhere.
   *
   * @see \Drupal\ddd_hook_test\Hook\DHooks
   */
  #[Hook('test_hook')]
  public function testHookRemoved(): string {
    return __METHOD__;
  }

}
