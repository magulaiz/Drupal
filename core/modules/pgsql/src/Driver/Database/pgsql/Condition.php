<?php

declare(strict_types=1);

namespace Drupal\pgsql\Driver\Database\pgsql;

use Drupal\Core\Database\Query\Condition as QueryCondition;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\PlaceholderInterface;

/**
 * Postgres implementation of \Drupal\Core\Database\Query\Condition.
 */
class Condition extends QueryCondition {

  /**
   * {@inheritdoc}
   */
  protected function processJsonCondition(array $condition, Connection $connection, bool &$ignore_operator, PlaceholderInterface $query_placeholder): string {
    $ignore_operator = TRUE;
    $op = $condition['operator'];
    if ($op === '=') {
      $op = '==';
    }
    // @todo Security - determine if this is sufficient escaping.
    $value = in_array(gettype($condition['value']), ['string', 'boolean'])
      ? json_encode($condition['value'], JSON_THROW_ON_ERROR)
      : $condition['value'];
    // PDO requires the ? be doubled else they are considered placeholders.
    // Inside the single-quoted jsonpath expression, we can't use a named
    // placeholder, as would normally be preferred. (It won't be interpreted.)
    return "{$condition['field']} @?? '{$condition['jsonpath']} ? (@ {$op} {$value})'";
  }

}
