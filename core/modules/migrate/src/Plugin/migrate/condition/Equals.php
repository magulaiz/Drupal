<?php

namespace Drupal\migrate\Plugin\migrate\condition;

/**
 * Provides an equals condition.
 *
 * Available configuration keys:
 * - value: (one of value or property is required) The literal value to which
 *   to compare the source.
 * - property: (one of value or property is required) The source or destination
 *   property key to 'get' and to compare the source.
 * - identical: (optional) Pass TRUE to compare with ===.
 *
 * Examples:
 *
 * Skip row if source_field is exactly equal to 0.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: equals
 *     method: row
 *     configuration:
 *       value: 0
 *       identical: TRUE
 *     source: source_field
 * @endcode
 *
 * Skip process if source_field is equal to the value of
 * another_source_field.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: equals
 *     method: process
 *     configuration:
 *       property: another_source_field
 *     source: source_field
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateConditionInterface
 *
 * @MigrateConditionPlugin(
 *   id = "equals"
 * )
 */
class Equals extends SimpleComparisonBase {

  /**
   * {@inheritdoc}
   */
  public function compare($source, $value) {
    if (isset($this->configuration['identical']) && $this->configuration['identical']) {
      return $source === $value;
    }
    else {
      return $source == $value;
    }
  }

}
