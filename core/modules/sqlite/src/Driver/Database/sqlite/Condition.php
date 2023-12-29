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
  }

  /**
   * {@inheritdoc}
   */
  protected function getJsonFieldFragment(string $field_name, array $condition, Connection $connection, PlaceholderInterface $query_placeholder, bool $enableOptimization = TRUE): string {
    assert($connection instanceof SQLiteConnection);
    return $this->doGetJsonFieldFragment($field_name, $condition, $connection, $query_placeholder, $connection->supportsGeneratedColumns());
  }

}
