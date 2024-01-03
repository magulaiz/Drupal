<?php

declare(strict_types=1);

namespace Drupal\mysql\Driver\Database\mysql;

use Drupal\Core\Database\JsonpathGeneratedFieldConditionTrait;
use Drupal\Core\Database\Query\Condition as QueryCondition;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\PlaceholderInterface;
use Drupal\mysql\Driver\Database\mysql\Connection as MySqlConnection;

/**
 * MySQL implementation of \Drupal\Core\Database\Query\Condition.
 */
class Condition extends QueryCondition {

  use JsonpathGeneratedFieldConditionTrait {
    getJsonFieldFragment as doGetJsonFieldFragment;
    getJsonFieldFragmentFunction as doGetJsonFieldFragmentFunction;
  }

  /**
   * {@inheritdoc}
   */
  protected function getJsonFieldFragment($field_name, array $condition, Connection $connection, PlaceholderInterface $query_placeholder): string {
    assert($connection instanceof MySqlConnection);
    return $this->doGetJsonFieldFragment($field_name, $condition, $connection, $query_placeholder, $connection->isMariaDb());
  }

  /**
   * {@inheritdoc}
   */
  protected function getJsonFieldFragmentFunction(string $field, string $jsonpath, mixed $value, Connection $connection): string {
    assert($connection instanceof MySqlConnection);
    $fragment = $this->doGetJsonFieldFragmentFunction($field, $jsonpath, $value, $connection);
    return $fragment
      // MySQL will otherwise cast the result to an int, so be explicit.
      . (!$connection->isMariaDb() && is_bool($value) ? ' = true' : '');
  }

}
