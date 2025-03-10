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
  public function parseTableIdentifier(string $identifier): array {
    $parts = explode(".", $identifier);
    [$database, $schema, $table] = match (count($parts)) {
      1 => [
        NULL,
        NULL,
        $this->canonicalizeIdentifier($parts[0], IdentifierType::Table),
      ],
      2 => [
        NULL,
        $this->schema($parts[0]),
        $this->canonicalizeIdentifier($parts[1], IdentifierType::Table),
      ],
      3 => [
        $this->database($parts[0]),
        $this->schema($parts[1]),
        $this->canonicalizeIdentifier($parts[2], IdentifierType::Table),
      ],
    };
    if ($this->tablePrefix !== '' && count($parts) === 1) {
      $database = $this->database(rtrim($this->tablePrefix, '.'));
    }
    $needsPrefix = FALSE;
    return [$database, $schema, $table, $needsPrefix];
  }

  /**
   * {@inheritdoc}
   */
  public function getTableMachineName(Table $table): string {
    $ret = isset($table->database) ? $this->quote($table->database->canonical()) . '.' : '';
    $ret .= $this->quote($table->canonicalName);
    return $ret;
  }

}
