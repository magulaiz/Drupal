<?php

declare(strict_types=1);

namespace Drupal\TestTools\Trait;

use Drupal\TestTools\PhpUnitCompatibility\IgnoreDeprecation;
use PHPUnit\Event\Code\TestMethodBuilder;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

// cspell:ignore errno errstr errfile errline

/**
 * Manage expected deprecations.
 *
 * @internal
 */
trait ExpectDeprecationTrait {

  /**
   * The previous error handler.
   *
   * @var callable|null
   */
  protected $previouslyDefinedErrorHandler;

  protected array $expectedDeprecations = [];
  protected array $collectedDeprecations = [];

  #[Before]
  public function setUpErrorHandler(): void {
    if ($this->previouslyDefinedErrorHandler === NULL) {
      // Get current handler.
      $handler = set_error_handler('var_dump');
      restore_error_handler();

      $this->previouslyDefinedErrorHandler = set_error_handler(
        function (int $errno, string $errstr, string $errfile = NULL, int $errline = NULL) use ($handler): bool {
          // Collect deprecations regardless of whether they are ignored or not.
          if (E_USER_DEPRECATED === $errno || E_DEPRECATED === $errno) {
            $this->collectedDeprecations[] = $errstr;
          }

          if ((E_USER_DEPRECATED === $errno || E_DEPRECATED === $errno) && $this->isTestInLegacyGroup()) {
            // dump(['Test level legacy', $errno, $errstr, $errfile, $errline]);
            return TRUE;
          }
          else {
            // dump(['Test level fallback', $errno, $errstr, $errfile, $errline]);
            call_user_func($handler, $errno, $errstr, $errfile, $errline);
          }
          return TRUE;
        }
      );
    }
  }

  #[After]
  public function tearDownErrorHandler(): void {
    if ($this->previouslyDefinedErrorHandler !== NULL) {
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

  protected function isTestInLegacyGroup(): bool {
    $groups = [];
    foreach ($this->valueObjectForEvents()->metadata()->isGroup() as $metadata) {
      $groups[] = $metadata->groupName();
    }
    return in_array('legacy', $groups, TRUE);
  }

}
