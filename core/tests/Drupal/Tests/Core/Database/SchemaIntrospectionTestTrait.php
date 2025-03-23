<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Database;

use Drupal\Core\Database\Identifier\Table as TableIdentifier;

/**
 * Provides methods for testing database schema characteristics.
 */
trait SchemaIntrospectionTestTrait {

  /**
   * Checks that an index covering exactly the given column names exists.
   *
   * @param string $table_name
   *   A non-prefixed table name.
   * @param array $column_names
   *   An array of column names.
   * @param string $index_type
   *   (optional) The type of the index. Can be one of 'index', 'unique' or
   *   'primary'. Defaults to 'index'.
   */
  protected function assertIndexOnColumns($table_name, array $column_names, $index_type = 'index') {
    if (!$table_name instanceof TableIdentifier) {
      @trigger_error("Passing a table identifier as a string to " . __METHOD__ . "() is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Pass a Table identifier value object instead. See https://www.drupal.org/node/7654132", E_USER_DEPRECATED);
      $table = $this->connection->identifiers->table($table_name);
    }
    else {
      $table = $table_name;
    }
    assert($table instanceof TableIdentifier);

    foreach ($this->getIndexColumnNames($table_name, $index_type) as $index_columns) {
      if ($column_names == $index_columns) {
        $this->assertTrue(TRUE);
        return;
      }
    }
    $this->assertTrue(FALSE);
  }

  /**
   * Checks that an index covering exactly the given column names doesn't exist.
   *
   * @param string $table_name
   *   A non-prefixed table name.
   * @param array $column_names
   *   An array of column names.
   * @param string $index_type
   *   (optional) The type of the index. Can be one of 'index', 'unique' or
   *   'primary'. Defaults to 'index'.
   */
  protected function assertNoIndexOnColumns($table_name, array $column_names, $index_type = 'index') {
    if (!$table_name instanceof TableIdentifier) {
      @trigger_error("Passing a table identifier as a string to " . __METHOD__ . "() is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Pass a Table identifier value object instead. See https://www.drupal.org/node/7654132", E_USER_DEPRECATED);
      $table = $this->connection->identifiers->table($table_name);
    }
    else {
      $table = $table_name;
    }
    assert($table instanceof TableIdentifier);

    foreach ($this->getIndexColumnNames($table, $index_type) as $index_columns) {
      if ($column_names == $index_columns) {
        $this->assertTrue(FALSE);
      }
    }
    $this->assertTrue(TRUE);
  }

  /**
   * Returns the column names used by the indexes of a table.
   *
   * @param string $table_name
   *   A table name.
   * @param string $index_type
   *   The type of the index. Can be one of 'index', 'unique' or 'primary'.
   *
   * @return array
   *   A multi-dimensional array containing the column names for each index of
   *   the given type.
   */
  protected function getIndexColumnNames($table_name, $index_type) {
    if (!$table_name instanceof TableIdentifier) {
      @trigger_error("Passing a table identifier as a string to " . __METHOD__ . "() is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Pass a Table identifier value object instead. See https://www.drupal.org/node/7654132", E_USER_DEPRECATED);
      $table = $this->connection->identifiers->table($table_name);
    }
    else {
      $table = $table_name;
    }
    assert($table instanceof TableIdentifier);

    assert(in_array($index_type, ['index', 'unique', 'primary'], TRUE));

    $schema = \Drupal::database()->schema();
    $introspect_index_schema = new \ReflectionMethod(get_class($schema), 'introspectIndexSchema');
    $index_schema = $introspect_index_schema->invoke($schema, $table);

    // Filter the indexes by type.
    if ($index_type === 'primary') {
      $indexes = [$index_schema['primary key']];
    }
    elseif ($index_type === 'unique') {
      $indexes = array_values($index_schema['unique keys']);
    }
    else {
      $indexes = array_values($index_schema['indexes']);
    }

    return $indexes;
  }

}
