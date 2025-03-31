<?php

namespace Drupal\mysqli\Driver\Database\mysqli;

use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\Database\SchemaObjectExistsException;
use Drupal\Core\Database\SchemaObjectDoesNotExistException;
use Drupal\mysql\Driver\Database\mysql\Schema as BaseMySqlSchema;

// cspell:ignore gipk

/**
 * MySQLi implementation of \Drupal\Core\Database\Schema.
 */
class Schema extends BaseMySqlSchema {

  // @phpcs:disable
  /**
   * {@inheritdoc}
   */
  // @phpstan-ignore-next-line missingType.return
  public function addField($table, $field, $spec, $keys_new = []) {
  // @phpcs:enable
  if (!$this->tableExists($table)) {
      throw new SchemaObjectDoesNotExistException("Cannot add field '$table.$field': table doesn't exist.");
    }
    if ($this->fieldExists($table, $field)) {
      throw new SchemaObjectExistsException("Cannot add field '$table.$field': field already exists.");
    }

    // Fields that are part of a PRIMARY KEY must be added as NOT NULL.
    $is_primary_key = isset($keys_new['primary key']) && in_array($field, $keys_new['primary key'], TRUE);
    if ($is_primary_key) {
      $this->ensureNotNullPrimaryKey($keys_new['primary key'], [$field => $spec]);
    }

    $fix_null = FALSE;
    if (!empty($spec['not null']) && !isset($spec['default']) && !$is_primary_key) {
      $fix_null = TRUE;
      $spec['not null'] = FALSE;
    }
    $query = 'ALTER TABLE {' . $table . '} ADD ';
    $query .= $this->createFieldSql($field, $this->processField($spec));
    if ($keys_sql = $this->createKeysSql($keys_new)) {
      // Make sure to drop the existing primary key before adding a new one.
      // This is only needed when adding a field because this method, unlike
      // changeField(), is supposed to handle primary keys automatically.
      if (isset($keys_new['primary key']) && $this->indexExists($table, 'PRIMARY')) {
        $query .= ', DROP PRIMARY KEY';
      }

      $query .= ', ADD ' . implode(', ADD ', $keys_sql);
    }
    try {
      $this->executeDdlStatement($query);
    }
    catch (DatabaseExceptionWrapper $e) {
      // MySQL error number 4111 (ER_DROP_PK_COLUMN_TO_DROP_GIPK) indicates that
      // when dropping and adding a primary key, the generated invisible primary
      // key (GIPK) column must also be dropped.
      if ($e->getPrevious() instanceof \mysqli_sql_exception &&
        $e->getPrevious()->getCode() === 4111 &&
        isset($keys_new['primary key']) &&
        $this->indexExists($table, 'PRIMARY') &&
        $this->findPrimaryKeyColumns($table) === ['my_row_id']
      ) {
        $this->connection->query($query . ', DROP COLUMN [my_row_id]');
      }
      else {
        throw $e;
      }
    }

    if (isset($spec['initial_from_field'])) {
      if (isset($spec['initial'])) {
        $expression = 'COALESCE(' . $spec['initial_from_field'] . ', :default_initial_value)';
        $arguments = [':default_initial_value' => $spec['initial']];
      }
      else {
        $expression = $spec['initial_from_field'];
        $arguments = [];
      }
      $this->connection->update($table)
        ->expression($field, $expression, $arguments)
        ->execute();
    }
    elseif (isset($spec['initial'])) {
      $this->connection->update($table)
        ->fields([$field => $spec['initial']])
        ->execute();
    }
    if ($fix_null) {
      $spec['not null'] = TRUE;
      $this->changeField($table, $field, $field, $spec);
    }
  }

}
