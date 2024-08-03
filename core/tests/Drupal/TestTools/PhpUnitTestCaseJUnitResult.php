<?php

declare(strict_types=1);

namespace Drupal\TestTools;

/**
 * @todo add doc.
 */
enum PhpUnitTestCaseJUnitResult: string {

  case Pass = 'pass';
  case Fail = 'fail';
  case Error = 'error';
  case Skip = 'skipped';

}
