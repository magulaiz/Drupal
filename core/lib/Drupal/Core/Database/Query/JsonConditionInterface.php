<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Query;

interface JsonConditionInterface extends StrictSqlParamsConditionInterface {

  /**
   * Builds a conditional clause on a JSON-backed field.
   *
   * @todo Validate this docblock, it is copied from ::condition() and needs updating.
   *
   * @param string $field
   *   The name of the field to check.
   * @param string $jsonpath
   *   The jsonpath for value comparison.
   * @param string|int|array|SelectInterface|bool|null $value
   *   The value to test the field against. In most cases, and depending on the
   *   operator, this will be a scalar or an array. As SQL accepts select
   *   queries on any place where a scalar value or set is expected, $value may
   *   also be a SelectInterface or an array of SelectInterfaces. If $operator
   *   is a unary operator, e.g. IS NULL, $value will be ignored and should be
   *   null. If the operator requires a subquery, e.g. EXISTS, the $field will
   *   be ignored and $value should be a SelectInterface object.
   * @param string|null $operator
   *   The operator to use. Supported for all supported databases are at least:
   *   - The comparison operators =, <>, !=, <, <=, >, >=.
   *   Defaults to =.
   *
   * @return $this
   *   The called object.
   *
   * @throws \Drupal\Core\Database\InvalidQueryException
   *   If passed invalid arguments, such as an empty array as $value.
   */
  public function jsonCondition(string $field, string $jsonpath, string|int|float|array|SelectInterface|bool|null $value = NULL, string $operator = '=');

}
