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

  protected bool $expectedError = FALSE;
  protected ?string $expectedErrorMessage = NULL;
  protected ?string $expectedErrorMessageRegularExpression = NULL;

  protected bool $actualError = FALSE;
  protected ?string $actualErrorMessage = NULL;

  public function setUpErrorHandler(): void {
    $this->setUpIgnoreDeprecationPatterns();

    if ($this->previouslyDefinedErrorHandler === NULL) {
      // Get current handler.
      $handler = set_error_handler('var_dump');
      restore_error_handler();

      $this->previouslyDefinedErrorHandler = set_error_handler(
        function (int $errno, string $errstr, string $errfile = NULL, int $errline = NULL) use ($handler): bool {
          if ((E_USER_ERROR === $errno || E_ERROR === $errno) && $this->expectedError) {
            $this->actualError = TRUE;
            $this->actualErrorMessage = $errstr;
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
      if ($this->expectedError) {
        self::assertTrue($this->actualError, 'An error was expected, but it was not triggered');
      }
      restore_error_handler();
    }
  }

  public function expectError(): void {
    $this->expectedError = TRUE;
  }

  public function expectErrorMessage(string $message): void {
    $this->expectedError = TRUE;
    $this->expectedErrorMessage = $message;
  }

  public function expectErrorMessageMatches(string $regularExpression): void {
    $this->expectedError = TRUE;
    $this->expectedErrorMessageRegularExpression = $regularExpression;
  }

}
