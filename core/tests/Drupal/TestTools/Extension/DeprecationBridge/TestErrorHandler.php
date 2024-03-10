<?php

declare(strict_types=1);

namespace Drupal\TestTools\Extension\DeprecationBridge;

/**
 * @todo
 *
 * @internal
 */
final class TestErrorHandler {

  /**
   * @todo
   */
  public function __construct(
    private $parentHandler,
    private $testCase,
  ) {
  }

  /**
   * @todo
   */
  public function __invoke(int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool {
    // We are within a test execution. If we have a deprecation and the test is
    // a deprecation test, than we just collect the deprecation and return to
    // execution, since deprecations are expected.
    if ((E_USER_DEPRECATED === $errorNumber || E_DEPRECATED === $errorNumber) && DeprecationHandler::isDeprecationTest($this->testCase)) {
      DeprecationHandler::collectActualDeprecation($errorString);
      return TRUE;
    }

    // In all other cases (errors, warnings, deprecations in normal tests), we
    // fall back to the parent error handler, which is the one that was
    // registered in the test runner bootstrap (BootstrapErrorHandler).
    call_user_func($this->parentHandler, $errorNumber, $errorString, $errorFile, $errorLine);
    return TRUE;
  }

}
