<?php

declare(strict_types=1);

namespace Drupal\hk_b_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;

class BHooks {

  #[Hook('testhook')]
  public function testHook(): string {
    return __METHOD__;
  }

}
