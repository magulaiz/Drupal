<?php

declare(strict_types=1);

namespace Drupal\TestTools\PhpUnitCompatibility\PhpUnit10;

/**
 * Drupal's forward compatibility layer with multiple versions of PHPUnit.
 *
 * @internal
 */
trait TestCompatibilityTrait {

  /** @var null|callable */
  protected $previouslyDefinedErrorHandler;

  protected bool $expectedError = FALSE;

  protected bool $actualError = FALSE;
  protected ?string $actualErrorMessage = NULL;

  public function tearDownExpectedTriggeredErrors(): void {
    if (null !== $this->previouslyDefinedErrorHandler) {
      self::assertSame($this->expectedError, $this->actualError, $this->expectedError ?
        'An error was expected, but it was not triggered' :
        'An unexpected error was triggered'
      );
      restore_error_handler();
    }
  }

  public function expectError(): void {
    $this->expectedError = TRUE;
    if (null === $this->previouslyDefinedErrorHandler) {
      $this->previouslyDefinedErrorHandler = set_error_handler(
        function (
          int $code,
          string $message
        ) {
          if (E_USER_ERROR === $code || E_ERROR === $code) {
            $this->actualError = TRUE;
            $this->actualErrorMessage = $message;
          }
          return true;
        }
      );
    }
  }

}
