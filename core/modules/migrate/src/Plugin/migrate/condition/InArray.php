<?php

namespace Drupal\migrate\Plugin\migrate\condition;

/**
 * Provides in_array condition.
 *
 * Available configuration keys:
 * - value: (one of value or property is required) The literal array in which
 *   to search for the source.
 * - property: (one of value or property is required) The source or destination
 *   property key to 'get' and then search for the source.
 * - strict: (optional) 'strict' parameter for in_array(). Defaults to FALSE.
 *
 * Examples:
 *
 * Skip row if the source_field is 'one', 'two', or 'three'.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: in_array
 *     method: row
 *     configuration:
 *       value:
 *         - one
 *         - two
 *         - three
 *       strict: TRUE
 *     source: source_field
 * @endcode
 *
 * Skip process if the value of source_field is found within the
 * value of another_source_field.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: in_array
 *     method: process
 *     configuration:
 *       property: another_source_field
 *     source: source_field
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateConditionInterface
 *
 * @MigrateConditionPlugin(
 *   id = "in_array"
 * )
 */
class InArray extends SimpleComparisonBase {

  /**
   * {@inheritdoc}
   */
  public function compare($source, $value) {
    return in_array($source, (array) $value, $this->configuration['strict'] ?? FALSE);
  }

}
