<?php

declare(strict_types=1);

namespace Drupal\TestTools\Extension\DeprecationBridge;

use PHPUnit\Event\Code\NoTestCaseObjectOnCallStackException;
use PHPUnit\Runner\ErrorHandler;

/**
 * @todo
 *
 * @internal
 */
final class BootstrapErrorHandler {

  private ErrorHandler $phpUnitErrorHandler;

  /**
   * @todo
   */
  public function __construct() {
    $this->phpUnitErrorHandler = new ErrorHandler();
  }

  /**
   * @todo
   */
  public function __invoke(int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool {
    // We collect a deprecation no matter what.
    if (E_USER_DEPRECATED === $errorNumber || E_DEPRECATED === $errorNumber) {
      DeprecationHandler::collectActualDeprecation($errorString);
    }

    // If the deprecation handled is one of those in the ignore list, we keep
    // running.
    if ((E_USER_DEPRECATED === $errorNumber || E_DEPRECATED === $errorNumber) && DeprecationHandler::isIgnoredDeprecation($errorString)) {
      return TRUE;
    }

    // In all other cases (errors, warnings, deprecations to be reported), we
    // fall back to PHPUnit's error handler, an instance of which was created
    // when this error handler was created.
    try {
      call_user_func($this->phpUnitErrorHandler, $errorNumber, $errorString, $errorFile, $errorLine);
    }
    catch (NoTestCaseObjectOnCallStackException $e) {
      // If we end up here, it's likely because a test's processing has
      // finished already and we are processing an error that occurred while
      // dealing with STDOUT rewinding or truncating. Do nothing.
    }
    return TRUE;
  }

}
