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
  }

  /**
   * {@inheritdoc}
   */
  protected function getJsonFieldFragment($field_name, array $condition, Connection $connection, PlaceholderInterface $query_placeholder): string {
    assert($connection instanceof MySqlConnection);
    return $this->doGetJsonFieldFragment($field_name, $condition, $connection, $query_placeholder, $connection->isMariaDb());
  }

}
