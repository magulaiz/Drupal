<?php

declare(strict_types = 1);

namespace Drupal\hk_a_test\Hook;

class ModuleImplementsAlter {

  private static ?\Closure $callback = NULL;

  /**
   * @param \Closure(array<string, string|false>&, string): void $callback
   */
  public static function set(\Closure $callback): void {
    self::$callback = $callback;
  }

  /**
   * @param array<string, string|false> $implementations
   * @param string $hook
   */
  public static function call(array &$implementations, string $hook): void {
    if (self::$callback === NULL) {
      return;
    }
    (self::$callback)($implementations, $hook);
  }

}
