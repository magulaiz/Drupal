<?php

declare(strict_types=1);

namespace Drupal\sqlite\Driver\Database\sqlite;

use Drupal\Core\Database\Identifier\IdentifierHandlerBase;
use Drupal\Core\Database\Identifier\IdentifierType;
use Drupal\Core\Database\Identifier\Table;

/**
 * SQLite implementation of the identifier handler.
 */
class IdentifierHandler extends IdentifierHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function getMaxLength(IdentifierType $type): int {
    // There is no hard limit on identifier length in SQLite, so we just use
    // common sense.
    // @see https://www.sqlite.org/limits.html
    // @see https://stackoverflow.com/questions/8135013/table-name-limit-in-sqlite-android
    return 256;
  }

  /**
   * {@inheritdoc}
   */
  public function parseTableIdentifier(string $identifier): array {
    $parts = parent::parseTableIdentifier($identifier);
    if ($this->tablePrefix !== '' && $parts['database'] === NULL && $parts['schema'] === NULL) {
      $parts['schema'] = $this->schema(rtrim($this->tablePrefix, '.'));
      $parts['needs_prefix'] = FALSE;
    }
    return $parts;
  }

}
