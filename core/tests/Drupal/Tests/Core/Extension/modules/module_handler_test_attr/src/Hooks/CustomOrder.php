<?php

declare(strict_types = 1);

namespace Drupal\module_handler_test_attr\Hooks;

use Drupal\Core\Attribute\Hook\Hook;

class CustomOrder {

  #[Hook('custom_order')]
  public function attrModule(): string {
    return __METHOD__;
  }

  #[Hook('custom_order', before: 'module_handler_test1')]
  public function beforeTest1(): string {
    return __METHOD__;
  }

  #[Hook('custom_order', after: 'module_handler_test1')]
  public function afterTest1(): string {
    return __METHOD__;
  }

  #[Hook('custom_order', weight: -5)]
  public function negativeWeight(): string {
    return __METHOD__;
  }

  #[Hook('custom_order', weight: 17)]
  public function positiveWeight(): string {
    return __METHOD__;
  }

  #[Hook('custom_order', module: 'module_handler_test1')]
  public function onBehalfOfTest1(): string {
    return __METHOD__;
  }

}
