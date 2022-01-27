<?php

namespace Drupal\migrate\Plugin\migrate\condition;

use Drupal\migrate\Row;

/**
 * Provides isset() condition.
 *
 * Examples:
 *
 * Skip a row if the source_value is not set.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: isset
 *     negate: true
 *     source: source_field
 *     method: row
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateConditionInterface
 *
 * @MigrateConditionPlugin(
 *   id = "isset"
 * )
 */
class IssetCondition extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate($source, Row $row) {
    return isset($source);
  }

}
