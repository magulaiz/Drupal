<?php

declare(strict_types=1);

namespace Drupal\TestTools\PhpUnitCompatibility\PhpUnit10;

/**
 * Drupal's forward compatibility layer with multiple versions of PHPUnit.
 *
 * @internal
 */
trait TestCompatibilityTrait {

  /*
   * The previous error handler.
   *
   * @var callable|null
   */
  protected $previouslyDefinedErrorHandler;

  protected bool $expectedError = FALSE;

  protected bool $actualError = FALSE;
  protected ?string $actualErrorMessage = NULL;

  public function tearDownExpectedTriggeredErrors(): void {
    if ($this->previouslyDefinedErrorHandler !== NULL) {
      self::assertSame($this->expectedError, $this->actualError, $this->expectedError ?
        'An error was expected, but it was not triggered' :
        'An unexpected error was triggered'
      );
      restore_error_handler();
    }
  }

  public function expectError(): void {
    $this->expectedError = TRUE;
    if ($this->previouslyDefinedErrorHandler === NULL) {
      $this->previouslyDefinedErrorHandler = set_error_handler(
        function (
          int $code,
          string $message
        ) {
          if (E_USER_ERROR === $code || E_ERROR === $code) {
            $this->actualError = TRUE;
            $this->actualErrorMessage = $message;
          }
          return TRUE;
        }
      );
    }
  }

}
