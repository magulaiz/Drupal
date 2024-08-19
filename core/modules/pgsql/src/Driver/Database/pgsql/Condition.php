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
    $valueType = gettype($condition['value']);
    if ($op === '=') {
      $op = '==';
    }
    $value = in_array($valueType, ['string', 'boolean', 'array'])
      // These values are not bound using a named parameter, however encoding
      // string values in particular provides a hedge against SQL injection.
      ? json_encode($condition['value'], JSON_THROW_ON_ERROR)
      : $condition['value'];
    return $op === '@>'
      // Containment.
      ? "JSONB_PATH_QUERY_ARRAY({$condition['field']}, '{$condition['jsonpath']}') @> '[{$value}]'"
      // PDO requires the ? be doubled else they are considered placeholders.
      // Inside the single-quoted jsonpath expression, we can't use a named
      // placeholder, as would normally be preferred. (It won't be interpreted.)
      : "{$condition['field']} @?? '{$condition['jsonpath']} ? (@ {$op} {$value})'";
  }

}
