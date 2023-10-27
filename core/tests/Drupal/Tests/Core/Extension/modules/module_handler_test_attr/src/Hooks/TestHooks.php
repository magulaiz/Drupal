<?php

declare(strict_types = 1);

namespace Drupal\module_handler_test_attr\Hooks;

use Drupal\Core\Attribute\Hook\Alter;
use Drupal\Core\Attribute\Hook\Hook;

class TestHooks {

  #[Hook('merge')]
  public function merge(): array {
    return [__METHOD__];
  }

  #[Hook('merge'), Hook('merge')]
  public function mergeTwice(): array {
    return [__METHOD__];
  }

  #[Hook('byref')]
  public function byref(array &$values): void {
    $values[] = __METHOD__;
  }

  #[Hook('byref', after: 'module_handler_test_attr')]
  public function byrefAfter(array &$values): void {
    $values[] = __METHOD__;
  }

  #[Hook('byref', after: 'module_handler_test')]
  public function byrefAfterOther(array &$values): void {
    $values[] = __METHOD__;
  }

  #[Hook('byref', before: 'module_handler_test_attr')]
  public function byrefBefore(array &$values): void {
    $values[] = __METHOD__;
  }

  #[Hook('byref')]
  public function byref2(array &$values): void {
    $values[] = __METHOD__;
  }

  #[Hook('byref', weight: -5)]
  public function byrefNegWeight(array &$values): void {
    $values[] = __METHOD__;
  }

  #[Hook('byref', module: 'module_handler_test')]
  public function byrefOnBehalfOf(array &$values): void {
    $values[] = __METHOD__;
  }

  #[Hook('(node|user)_(update|insert)')]
  public function nodeOrUserUpdateOrInsert(): string {
    return __METHOD__;
  }

  #[Alter('(node|user)_view')]
  public function nodeOrUserViewAlter(array &$values): void {
    $values[] = __METHOD__;
  }

}
