<?php

declare(strict_types = 1);

namespace Drupal\module_handler_test_attr\Hooks;

use Drupal\Core\Attribute\Hook\Alter;
use Drupal\Core\Attribute\Hook\Hook;

class TypeAlter {

  #[Alter('type')]
  public function alter(array &$value): void {
    $value[] = __METHOD__;
  }

  #[Hook('type_alter')]
  public function alterWithHookAttribute(array &$value): void {
    $value[] = __METHOD__;
  }

  #[Alter('subtype')]
  public function alterSubtype(array &$value): void {
    $value[] = __METHOD__;
  }

}
