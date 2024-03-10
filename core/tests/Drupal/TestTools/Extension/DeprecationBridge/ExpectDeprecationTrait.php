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

  protected array $expectedDeprecations = [];
  public array $collectedDeprecations = [];

  #[Before]
  public function setUpErrorHandler(): void {
    set_error_handler(new TestErrorHandler(DeprecationHandler::currentErrorHandler(), $this));
  }

  #[After]
  public function tearDownErrorHandler(): void {
    if (DeprecationHandler::currentErrorHandler() instanceof TestErrorHandler) {
      restore_error_handler();
    }

    // Checks if collected deprecations match the expectations.
    if ($this->expectedDeprecations) {
      $prefix = "@expectedDeprecation:\n";
      $expDep = $prefix . '%A  ' . implode("\n%A  ", $this->expectedDeprecations) . "\n%A";
      $actDep = $prefix . '  ' . implode("\n  ", $this->collectedDeprecations) . "\n";
      $this->assertStringMatchesFormat($expDep, $actDep);
    }
  }

  public function expectDeprecation(string $message): void {
    if (!$this->valueObjectForEvents()->metadata()->isIgnoreDeprecations()->isNotEmpty() && !$this->isTestInLegacyGroup()) {
      throw new \RuntimeException('expectDeprecation() can only be called from tests marked with #[IgnoreDeprecations] or \'@group legacy\'');
    }
    $this->expectedDeprecations[] = $message;
  }

  public function isTestInLegacyGroup(): bool {
    $groups = [];
    foreach ($this->valueObjectForEvents()->metadata()->isGroup() as $metadata) {
      $groups[] = $metadata->groupName();
    }
    return in_array('legacy', $groups, TRUE);
  }

}
