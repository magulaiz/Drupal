<?php

declare(strict_types=1);

namespace Drupal\hk_d_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Attribute\RemoveHook;
use Drupal\Core\Hook\Attribute\ReOrderHook;
use Drupal\Core\Hook\Order;
use Drupal\hk_c_test\Hook\CHooks;

#[ReOrderHook('testhook', CHooks::class, 'testHookReOrderFirst', Order::First)]
#[RemoveHook('testhook', CHooks::class, 'testHookRemoved')]
class DHooks {

  #[Hook('testhook')]
  public function testHook(): string {
    return __METHOD__;
  }

}
