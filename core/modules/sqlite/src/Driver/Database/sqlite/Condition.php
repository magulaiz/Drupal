<?php

declare(strict_types=1);

namespace Drupal\sqlite\Driver\Database\sqlite;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\JsonpathGeneratedFieldConditionTrait;
use Drupal\Core\Database\Query\Condition as QueryCondition;
use Drupal\Core\Database\Query\PlaceholderInterface;
use Drupal\sqlite\Driver\Database\sqlite\Connection as SQLiteConnection;

/**
 * SQLite implementation of \Drupal\Core\Database\Query\Condition.
 */
class Condition extends QueryCondition {

  use JsonpathGeneratedFieldConditionTrait {
    getJsonFieldFragment as doGetJsonFieldFragment;
    getJsonFieldFragmentFunction as doGetJsonFieldFragmentFunction;
  }

  /**
   * {@inheritdoc}
   */
  protected function processJsonCondition(array $condition, Connection $connection, bool &$ignore_operator, PlaceholderInterface $query_placeholder): string {
    // See special-casing for booleans in ::getJsonFieldFragmentFunction().
    $ignore_operator = is_bool($condition['value']);
    return parent::processJsonCondition($condition, $connection, $ignore_operator, $query_placeholder);
  }

  /**
   * {@inheritdoc}
   */
  protected function getJsonFieldFragment(string $field_name, array $condition, Connection $connection, PlaceholderInterface $query_placeholder, bool $enableOptimization = TRUE): string {
    assert($connection instanceof SQLiteConnection);
    return $this->doGetJsonFieldFragment($field_name, $condition, $connection, $query_placeholder, $connection->supportsGeneratedColumns());
  }

  /**
   * {@inheritdoc}
   */
  protected function getJsonFieldFragmentFunction(string $field, string $jsonpath, mixed $value, Connection $connection): string {
    // SQLite's json_extract() function returns integer 1/0 for boolean values.
    return $this->doGetJsonFieldFragmentFunction($field, $jsonpath, $value, $connection)
      . (is_bool($value) ? ' = ' . (int) $value : '');
  }

}
