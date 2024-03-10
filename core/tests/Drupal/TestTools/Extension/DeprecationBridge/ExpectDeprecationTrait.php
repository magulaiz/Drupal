<?php

declare(strict_types=1);

namespace Drupal\TestTools\Extension\DeprecationBridge;

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
    set_error_handler(new TestErrorHandler(DeprecationHandler::currentErrorHandler(), $this));
  }

  #[After]
  public function tearDownErrorHandler(): void {
    if (!DeprecationHandler::isEnabled()) {
      return;
    }

    if (DeprecationHandler::currentErrorHandler() instanceof TestErrorHandler) {
      restore_error_handler();
    }

    // Checks if collected deprecations match the expectations.
    if (DeprecationHandler::getExpectedDeprecations()) {
      $prefix = "@expectedDeprecation:\n";
      $expDep = $prefix . '%A  ' . implode("\n%A  ", DeprecationHandler::getExpectedDeprecations()) . "\n%A";
      $actDep = $prefix . '  ' . implode("\n  ", DeprecationHandler::getCollectedDeprecations()) . "\n";
      $this->assertStringMatchesFormat($expDep, $actDep);
    }
  }

  public function expectDeprecation(string $message): void {
    if (!DeprecationHandler::isEnabled()) {
      return;
    }

    if (!$this->valueObjectForEvents()->metadata()->isIgnoreDeprecations()->isNotEmpty() && !DeprecationHandler::isTestInLegacyGroup($this)) {
      throw new \RuntimeException('expectDeprecation() can only be called from tests marked with #[IgnoreDeprecations] or \'@group legacy\'');
    }
    DeprecationHandler::expectDeprecation($message);
  }

}
