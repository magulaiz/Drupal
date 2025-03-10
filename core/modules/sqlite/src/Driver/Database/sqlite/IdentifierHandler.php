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
    return 128;
  }

  /**
   * {@inheritdoc}
   */
  public function getTableMachineName(Table $table): string {
    if (!isset($table->database) && !isset($table->schema) && $table->needsPrefix) {
      return $this->quote(rtrim($this->tablePrefix, '.')) . '.' . $this->quote($table->canonicalName);
    }
    $ret = isset($table->schema) ? $this->quote($table->schema->canonical()) . '.' : '';
    $ret .= $this->quote($table->canonicalName);
    return $ret;
  }

}
