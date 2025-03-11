<?php

declare(strict_types = 1);

namespace Drupal\hk_a_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Order;
use Drupal\Core\Hook\OrderAfter;

class AHooks {

  #[Hook('testhook')]
  public function testHook(): string {
    return __METHOD__;
  }

  #[Hook('testhook', order: Order::First)]
  public function testHookFirst(): string {
    return __METHOD__;
  }

  #[Hook('testhook', order: Order::Last)]
  public function testHookLast(): string {
    return __METHOD__;
  }

  #[Hook('testhook', order: new OrderAfter(modules: ['hk_b_test']))]
  public function testHookAfterB(): string {
    return __METHOD__;
  }

}
