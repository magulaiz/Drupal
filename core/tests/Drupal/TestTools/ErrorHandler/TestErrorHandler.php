<?php

declare(strict_types=1);

namespace Drupal\TestTools\ErrorHandler;

use Drupal\TestTools\Extension\DeprecationBridge\DeprecationHandler;

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
    if (!DeprecationHandler::isEnabled()) {
      throw new \RuntimeException(__METHOD__ . '() must not be called if the deprecation handler is not enabled.');
    }

    // We are within a test execution. If we have a deprecation and the test is
    // a deprecation test, than we just collect the deprecation and return to
    // execution, since deprecations are expected.
    if ((E_USER_DEPRECATED === $errorNumber || E_DEPRECATED === $errorNumber) && DeprecationHandler::isDeprecationTest($this->testCase)) {
      $prefix = (error_reporting() & $errorNumber) ? 'Unsilenced deprecation: ' : '';
      DeprecationHandler::collectActualDeprecation($prefix . $errorString);
      return TRUE;
    }

    // In all other cases (errors, warnings, deprecations in normal tests), we
    // fall back to the parent error handler, which is the one that was
    // registered in the test runner bootstrap (BootstrapErrorHandler).
    call_user_func($this->parentHandler, $errorNumber, $errorString, $errorFile, $errorLine);
    return TRUE;
  }

}
