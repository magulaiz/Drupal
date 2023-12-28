<?php

declare(strict_types=1);

namespace Drupal\Core\Database;

use Drupal\Core\Database\Exception\SchemaIndexOnJsonFieldUnsupportedException;

/**
 * Trait for validating index creation attempts on JSON columns.
 */
trait JsonIndexValidationTrait {

  /**
   * Validator for indexes which may contain JSON data columns.
   *
   * @param string $table
   *   Table name.
   * @param string $index
   *   Index name.
   * @param array $fields
   *   Index field specification.
   * @param array $table_spec
   *   Table schema.
   *
   * @throws \Drupal\Core\Database\Exception\SchemaIndexOnJsonFieldUnsupportedException
   *   Thrown when there is a validation error.
   */
  protected static function guardNoDirectJsonIndexes(string $table, string $index, array $fields, array $table_spec): void {
    foreach ($fields as $field_spec) {
      $field_name = is_array($field_spec) ? $field_spec[0] : $field_spec;
      if ($table_spec['fields'][$field_name]['type'] === 'json') {
        throw SchemaIndexOnJsonFieldUnsupportedException::forColumn($table, $index, $field_name);
      }
    }
  }

}
