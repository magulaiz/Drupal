<?php

declare(strict_types = 1);

namespace Drupal\hk_c_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Order;
use Drupal\Core\Hook\OrderAfter;

class CHooks {

  #[Hook('testhook')]
  public function testHook(): string {
    return __METHOD__;
  }

  #[Hook('testhook', order: Order::First)]
  public function testHookFirst(): string {
    return __METHOD__;
  }

  /**
   * This implementation is reordered from elsewhere.
   *
   * @see \Drupal\hk_d_test\Hook\DHooks
   */
  #[Hook('testhook')]
  public function testHookReOrderFirst(): string {
    return __METHOD__;
  }

  /**
   * This implementation is removed from elsewhere.
   *
   * @see \Drupal\hk_d_test\Hook\DHooks
   */
  #[Hook('testhook')]
  public function testHookRemoved(): string {
    return __METHOD__;
  }



}
