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
    DeprecationHandler::reset();
    set_error_handler(new TestErrorHandler(DeprecationHandler::currentErrorHandler(), $this));
  }

  #[After]
  public function tearDownErrorHandler(): void {
    if (DeprecationHandler::currentErrorHandler() instanceof TestErrorHandler) {
      restore_error_handler();
    }

    // Checks if collected deprecations match the expectations.
    if (DeprecationHandler::expectedDeprecations()) {
      $prefix = "@expectedDeprecation:\n";
      $expDep = $prefix . '%A  ' . implode("\n%A  ", DeprecationHandler::expectedDeprecations()) . "\n%A";
      $actDep = $prefix . '  ' . implode("\n  ", DeprecationHandler::collectedDeprecations()) . "\n";
      $this->assertStringMatchesFormat($expDep, $actDep);
    }
  }

  public function expectDeprecation(string $message): void {
    if (!$this->valueObjectForEvents()->metadata()->isIgnoreDeprecations()->isNotEmpty() && !$this->isTestInLegacyGroup()) {
      throw new \RuntimeException('expectDeprecation() can only be called from tests marked with #[IgnoreDeprecations] or \'@group legacy\'');
    }
    DeprecationHandler::addExpectedDeprecation($message);
  }

  public function isTestInLegacyGroup(): bool {
    $groups = [];
    foreach ($this->valueObjectForEvents()->metadata()->isGroup() as $metadata) {
      $groups[] = $metadata->groupName();
    }
    return in_array('legacy', $groups, TRUE);
  }

}
