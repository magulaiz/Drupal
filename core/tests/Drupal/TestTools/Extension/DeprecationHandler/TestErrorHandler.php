<?php

declare(strict_types=1);

namespace Drupal\TestTools\Extension\DeprecationHandler;

use Drupal\TestTools\PhpUnitCompatibility\IgnoreDeprecation;
use PHPUnit\Event\Code\NoTestCaseObjectOnCallStackException;
use PHPUnit\Runner\ErrorHandler;

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
      $this->testCase->collectedDeprecations[] = $errorString;
    }

    if ((E_USER_DEPRECATED === $errorNumber || E_DEPRECATED === $errorNumber) && $this->testCase->isTestInLegacyGroup()) {
      // dump(['Test level legacy', $errorNumber, $errorString, $errorFile, $errorLine]);
      return TRUE;
    }
    else {
      // dump(['Test level fallback', $errorNumber, $errorString, $errorFile, $errorLine]);
      call_user_func($this->parentHandler, $errorNumber, $errorString, $errorFile, $errorLine);
    }
    return TRUE;
  }

}