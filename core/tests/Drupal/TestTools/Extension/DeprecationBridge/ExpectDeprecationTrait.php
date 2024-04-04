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
        is_object($handler) ? get_class($handler) : $handler,
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

}
