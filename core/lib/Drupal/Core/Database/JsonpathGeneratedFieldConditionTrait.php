<?php

declare(strict_types=1);

namespace Drupal\Core\Database;

trait JsonpathGeneratedFieldConditionTrait {

  /**
   * Get the field fragment for a jsonpath condition.
   *
   * This method adds an additional parameter to enable optimization on an
   * additional condition, and can be re-used between MySQL and SQLite.
   *
   * @param string $field_name
   *   Field name.
   * @param array $condition
   *   Condition definition.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param bool $enableOptimization
   *   Whether to enable optimization. Defaults to TRUE.
   *
   * @return string
   *   Field fragment.
   */
  protected function getJsonFieldFragment(string $field_name, array $condition, Connection $connection, bool $enableOptimization = TRUE): string {
    if ($enableOptimization) {
      // MariaDB's optimizer won't automatically select an applicable index
      // based on a generated column matching the JSON path. Query the generated
      // column, directly.
      $schema = $connection->schema();
      if (method_exists($schema, 'getJsonpathGeneratedFieldName')) {
        return $schema->getJsonpathGeneratedFieldName($field_name, $condition['jsonpath']);
      }
    }
    return "JSON_EXTRACT({$condition['field']}, '{$condition['jsonpath']}')";
  }

}
