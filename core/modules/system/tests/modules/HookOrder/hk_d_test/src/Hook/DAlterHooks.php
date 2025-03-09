<?php

declare(strict_types = 1);

namespace Drupal\hk_d_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;

class DAlterHooks {

  #[Hook('test_alter')]
  public function testAlter(array &$calls): void {
    $calls[] = __METHOD__;
  }

  #[Hook('test_subtype_alter')]
  public function testSubtypeAlter(array &$calls): void {
    $calls[] = __METHOD__;
  }

}
