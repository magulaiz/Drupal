<?php

namespace Drupal\migrate\Plugin\migrate\condition;

use Drupal\migrate\Row;

/**
 * Provides empty() condition.
 *
 * Examples:
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: empty
 *     source: source_field
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateConditionInterface
 *
 * @MigrateConditionPlugin(
 *   id = "empty"
 * )
 */
class EmptyCondition extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate($source, Row $row) {
    return empty($source);
  }

}
