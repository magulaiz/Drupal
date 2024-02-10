<?php

declare(strict_types=1);

namespace Drupal\TestTools\PhpUnitCompatibility\PhpUnit10;

// cspell:ignore errno errstr errfile errline

/**
 * Drupal's forward compatibility layer with multiple versions of PHPUnit.
 *
 * @internal
 */
trait TestCompatibilityTrait {

  /**
   * The previous error handler.
   *
   * @var callable|null
   */
  protected $previouslyDefinedErrorHandler;

  protected bool $expectedWarning = FALSE;
  protected ?string $expectedWarningMessage = NULL;
  protected ?string $expectedWarningMessageRegularExpression = NULL;

  protected bool $actualWarning = FALSE;
  protected ?string $actualWarningMessage = NULL;

  public function setUpErrorHandler(): void {
    $this->setUpIgnoreDeprecationPatterns();

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

          if ((E_USER_WARNING === $errno || E_WARNING === $errno) && $this->expectedWarning) {
            $this->actualWarning = TRUE;
            $this->actualWarningMessage = $errstr;
          }
          elseif ((E_USER_DEPRECATED === $errno || E_DEPRECATED === $errno) && $this->isIgnoredDeprecation($errstr)) {
            return TRUE;
          }
          elseif ((E_USER_DEPRECATED === $errno || E_DEPRECATED === $errno) && $this->isTestInLegacyGroup()) {
            return TRUE;
          }
          else {
            call_user_func($handler, $errno, $errstr, $errfile, $errline);
          }
          return TRUE;
        }
      );
    }
  }

  public function tearDownErrorHandler(): void {
    if ($this->previouslyDefinedErrorHandler !== NULL) {
      if ($this->expectedWarning) {
        self::assertTrue($this->actualWarning, 'A warning was expected, but it was not triggered');
        if (isset($this->expectedWarningMessage)) {
          self::assertStringContainsString($this->expectedWarningMessage, $this->actualWarningMessage, 'Actual warning message does not match expected');
        }
        elseif (isset($this->expectedWarningMessageRegularExpression)) {
          self::assertMatchesRegularExpression($this->expectedWarningMessageRegularExpression, $this->actualWarningMessage, 'Actual warning message does not match expected regular expression');
        }
      }
      restore_error_handler();
    }

    // Checks if collected deprecations match the expectations.
    $this->tearDownExpectedDeprecations();
  }

  public function expectWarning(): void {
    $this->expectedWarning = TRUE;
  }

  public function expectWarningMessage(string $message): void {
    $this->expectedWarning = TRUE;
    $this->expectedWarningMessage = $message;
  }

  public function expectWarningMessageMatches(string $regularExpression): void {
    $this->expectedWarning = TRUE;
    $this->expectedWarningMessageRegularExpression = $regularExpression;
  }

}
