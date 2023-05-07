<?php

namespace Drupal\migrate\Plugin\migrate\condition;

use Drupal\migrate\Row;

/**
 * Provides is_null() condition.
 *
 * Examples:
 *
 * Skip the row is th source_value is null.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: is_null
 *     source: source_field
 *     method: row
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateConditionInterface
 *
 * @MigrateConditionPlugin(
 *   id = "is_null"
 * )
 */
class IsNull extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate($source, Row $row) {
    return is_null($source);
  }

}
