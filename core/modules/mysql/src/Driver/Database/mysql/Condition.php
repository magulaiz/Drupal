<?php

namespace Drupal\mysql\Driver\Database\mysql;

use Drupal\Core\Database\JsonpathGeneratedFieldConditionTrait;
use Drupal\Core\Database\Query\Condition as QueryCondition;
use Drupal\Core\Database\Connection;
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
  protected function getJsonFieldFragment($field_name, array $condition, Connection $connection): string {
    assert($connection instanceof MySqlConnection);
    return $this->doGetJsonFieldFragment($field_name, $condition, $connection, $connection->isMariaDb());
  }

}
