<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Exception;

use Drupal\Core\Database\DatabaseException;
use Drupal\Core\Database\SchemaException;

/**
 * Exception for unsupported index creation directly on a JSON column.
 */
final class SchemaIndexOnJsonFieldUnsupportedException extends SchemaException implements DatabaseException {

  /**
   * Constructor.
   *
   * @param string $table
   *   Table name.
   * @param string $index
   *   Index name.
   * @param string $column
   *   Column name.
   */
  public static function forColumn(string $table, string $index, string $column) {
    return new self(sprintf(
      'Database does not support creating indexes directly on JSON data column: Index %s for table %s, JSON data column %s. Specify "json_hotpaths" in the schema, instead.',
      $index,
      $table,
      $column,
    ));
  }

}
