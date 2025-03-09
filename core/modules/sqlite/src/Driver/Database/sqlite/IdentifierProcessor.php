<?php

declare(strict_types=1);

namespace Drupal\sqlite\Driver\Database\sqlite;

use Drupal\Core\Database\Identifier\IdentifierProcessorBase;
use Drupal\Core\Database\Identifier\IdentifierType;

/**
 * SQLite implementation of the identifier processor.
 */
class IdentifierProcessor extends IdentifierProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function getMaxLength(IdentifierType $type): int {
    // There is no hard limit on identifier length in SQLite, so we just use
    // common sense.
    // @see https://www.sqlite.org/limits.html
    // @see https://stackoverflow.com/questions/8135013/table-name-limit-in-sqlite-android
    return 128;
  }

}
