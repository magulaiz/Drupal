<?php

declare(strict_types=1);

namespace Drupal\pgsql\Driver\Database\pgsql;

use Drupal\Core\Database\Query\Condition as QueryCondition;
use Drupal\Core\Database\Connection;

/**
 * Postgres implementation of \Drupal\Core\Database\Query\Condition.
 */
class Condition extends QueryCondition {

  /**
   * {@inheritdoc}
   */
  protected function processJsonCondition(array $condition, Connection $connection, bool &$ignore_operator): string {
    $ignore_operator = TRUE;
    $op = $condition['operator'];
    if ($op === '=') {
      $op = '==';
    }
    return "{$condition['field']} @? '{$condition['jsonpath']} ? (@ {$op} {$condition['value']})'";
  }

}
