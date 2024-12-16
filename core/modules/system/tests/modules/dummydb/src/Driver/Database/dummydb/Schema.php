<?php

declare(strict_types=1);

// cspell:ignore dummydb

namespace Drupal\dummydb\Driver\Database\dummydb;

use Drupal\Core\Database\Schema as DatabaseSchema;

/**
 * DummyDB implementation of \Drupal\Core\Database\Schema.
 */
class Schema extends DatabaseSchema {

  /**
   * {@inheritdoc}
   */
  public function getFieldTypeMap(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function renameTable($table, $new_name): void {}

  /**
   * {@inheritdoc}
   */
  public function dropTable($table): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function addField($table, $field, $spec, $keys_new = []): void {}

  /**
   * {@inheritdoc}
   */
  public function dropField($table, $field): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function indexExists($table, $name): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function addPrimaryKey($table, $fields): void {}

  /**
   * {@inheritdoc}
   */
  public function dropPrimaryKey($table):bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function addUniqueKey($table, $name, $fields): void {}

  /**
   * {@inheritdoc}
   */
  public function dropUniqueKey($table, $name): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function addIndex($table, $name, $fields, array $spec): void {}

  /**
   * {@inheritdoc}
   */
  public function dropIndex($table, $name): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function changeField($table, $field, $field_new, $spec, $keys_new = []): void {}

}
