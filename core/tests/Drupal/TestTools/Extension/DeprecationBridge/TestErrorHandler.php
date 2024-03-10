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
    // Collect deprecations regardless of whether they are ignored or not.
    if (E_USER_DEPRECATED === $errorNumber || E_DEPRECATED === $errorNumber) {
      DeprecationHandler::collectActualDeprecation($errorString);
    }

    if ((E_USER_DEPRECATED === $errorNumber || E_DEPRECATED === $errorNumber) && DeprecationHandler::isTestInLegacyGroup($this->testCase)) {
      return TRUE;
    }
    else {
      call_user_func($this->parentHandler, $errorNumber, $errorString, $errorFile, $errorLine);
    }
    return TRUE;
  }

}
