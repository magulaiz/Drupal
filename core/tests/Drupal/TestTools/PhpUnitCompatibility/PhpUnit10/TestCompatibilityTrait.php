<?php

declare(strict_types=1);

namespace Drupal\TestTools\PhpUnitCompatibility\PhpUnit10;

use PHPUnit\Metadata\Covers;

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

          if ((E_USER_DEPRECATED === $errno || E_DEPRECATED === $errno) && $this->isIgnoredDeprecation($errstr)) {
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
      restore_error_handler();
    }

    // Checks if collected deprecations match the expectations.
    $this->tearDownExpectedDeprecations();
  }

  /**
   * Gets @covers defined on the test class.
   *
   * @return string[]
   *   An array of classes listed with the @covers annotation.
   */
  public function getTestClassCovers(): array {
    $ret = [];
    foreach ($this->valueObjectForEvents()->metadata()->isCovers() as $metadata) {
      if ($metadata instanceof Covers) {
        $ret[] = $metadata->target();
      }
    }
    return $ret;
  }

}
