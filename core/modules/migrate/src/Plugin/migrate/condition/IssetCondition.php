<?php

namespace Drupal\migrate\Plugin\migrate\condition;

use Drupal\migrate\Row;

/**
 * Provides isset() condition.
 *
 * Examples:
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: isset
 *     source: source_field
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
