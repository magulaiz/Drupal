<?php

declare(strict_types=1);

namespace Drupal\hk_a_test\Hook;

/**
 * Alter implementations dynamically.
 */
class ModuleImplementsAlter {

  /**
   * @var \Closure
   */
  private static ?\Closure $callback = NULL;

  /**
   * @param \Closure(array<string, string|false>&, string): void $callback
   *   Set callbacks.
   */
  public static function set(\Closure $callback): void {
    self::$callback = $callback;
  }

  /**
   * @param array<string, string|false> $implementations
   *   The implementations.
   * @param string $hook
   *   The hook.
   */
  public static function call(array &$implementations, string $hook): void {
    if (self::$callback === NULL) {
      return;
    }
    (self::$callback)($implementations, $hook);
  }

}
