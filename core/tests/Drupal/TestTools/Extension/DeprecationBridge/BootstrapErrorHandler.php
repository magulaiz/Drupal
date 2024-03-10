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
    if ((E_USER_DEPRECATED === $errorNumber || E_DEPRECATED === $errorNumber) && DeprecationHandler::isIgnoredDeprecation($errorString)) {
      // Deprecation handled is one of those in the ignore list.
      return TRUE;
    }
    else {
      // Fallback to PHPUnit's error handler if no other processing.
      try {
        call_user_func($this->phpUnitErrorHandler, $errorNumber, $errorString, $errorFile, $errorLine);
      }
      catch (NoTestCaseObjectOnCallStackException $e) {
        // If we end up here, it's likely because the test processing has
        // finished already and we are processing an error that occurred
        // while dealing with STDOUT rewinding or truncating. Do nothing.
      }
    }
    return TRUE;
  }

}
