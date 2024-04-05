<?php

declare(strict_types=1);

namespace Drupal\TestTools\Extension\DeprecationBridge;

use Drupal\Core\Utility\Error;
use Drupal\TestTools\ErrorHandler\TestErrorHandler;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

/**
 * Manage expected deprecations.
 *
 * @internal
 */
trait ExpectDeprecationTrait {

  #[Before]
  public function setUpErrorHandler(): void {
    if (!DeprecationHandler::isEnabled()) {
      return;
    }

    DeprecationHandler::reset();
    set_error_handler(new TestErrorHandler(Error::currentErrorHandler(), $this));
  }

  #[After]
  public function tearDownErrorHandler(): void {
    if (!DeprecationHandler::isEnabled()) {
      return;
    }

    $handler = Error::currentErrorHandler();
    if (!$handler instanceof TestErrorHandler) {
      throw new \RuntimeException(sprintf('%s registered its own error handler (%s) without restoring the previous one before tear down. This can cause unpredictable test results. Ensure the test cleans up after itself.',
        $this->name(),
        self::getCallableName($handler);
      ));
    }
    restore_error_handler();

    // Checks if collected deprecations match the expectations.
    if (DeprecationHandler::getExpectedDeprecations()) {
      $prefix = "@expectedDeprecation:\n";
      $expDep = $prefix . '%A  ' . implode("\n%A  ", DeprecationHandler::getExpectedDeprecations()) . "\n%A";
      $actDep = $prefix . '  ' . implode("\n  ", DeprecationHandler::getCollectedDeprecations()) . "\n";
      $this->assertStringMatchesFormat($expDep, $actDep);
    }
  }

  public function expectDeprecation(string $message): void {
    if (!DeprecationHandler::isDeprecationTest($this)) {
      throw new \RuntimeException('expectDeprecation() can only be called from tests marked with #[IgnoreDeprecations] or \'@group legacy\'');
    }

    if (!DeprecationHandler::isEnabled()) {
      return;
    }

    DeprecationHandler::expectDeprecation($message);
  }

  /**
   * Returns a callable as a string suitable for inclusion in a message,
   *
   * @param callable $callable
   *   The callable.
   *
   * @return string
   *   The string suitable for inclusion in a message,
   */
  private static function getCallableName(callable $callable): string {
    switch (true) {
      case is_string($callable) && strpos($callable, '::'):
        return '[static] ' . $callable;

      case is_string($callable):
        return '[function] ' . $callable;

      case is_array($callable) && is_object($callable[0]):
        return '[method] ' . get_class($callable[0]) . '->' . $callable[1];

      case is_array($callable):
        return '[static] ' . $callable[0] . '::' . $callable[1];

      case $callable instanceof Closure:
        return '[closure]';

      case is_object($callable):
        return '[invokable] ' . get_class($callable);

      default:
        return '[unknown]';

    }
  }

}
