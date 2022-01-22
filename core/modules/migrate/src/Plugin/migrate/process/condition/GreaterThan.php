<?php

namespace Drupal\migrate\Plugin\migrate\process\condition;

/**
 * Provides a greater than condition.
 *
 * Available configuration keys:
 * - value: (one of value or property is required) The literal value to which
 *   to compare the source.
 * - property: (one of value or property is required) The source or destination
 *   property key to 'get' and to compare the source.
 *
 * Examples:
 *
 * Skip the row if source_field is less than or equal to 5.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     method: row
 *     condition: greater_than
 *     negate: true
 *     configuration:
 *       value: 5
 *     source: source_field
 * @endcode
 *
 * Skip the process if source_field is greater than the destination property
 * field_whatever.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     method: process
 *     condition: greater_than
 *     configuration:
 *       property: '@field_whatever'
 *     source: source_field
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface
 *
 * @MigrateProcessConditionPlugin(
 *   id = "greater_than"
 * )
 */
class GreaterThan extends SimpleComparisonBase {

  /**
   * {@inheritdoc}
   */
  public function compare($source, $value) {
    return $source > $value;
  }

}
