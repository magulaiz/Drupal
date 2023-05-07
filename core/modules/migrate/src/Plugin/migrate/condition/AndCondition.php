<?php

namespace Drupal\migrate\Plugin\migrate\condition;

use Drupal\migrate\Row;

/**
 * Provides an and condition.
 *
 * This condition allows logical combinations of multiple conditions.
 *
 * Configuration must be an array of configuration with the following available
 * keys.
 * - condition: The id of a MigrateCondition plugin.
 * - configuration: (optional) Additional configuration to be passed to the
 *   condition plugin. Some condition plugins have required configuration.
 * - negate: (optional) A boolean flag that indicates whether condition result
 *   should be negated. Defaults to FALSE.
 *
 * Examples:
 *
 * Set destination_field to TRUE if source_value is 5.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: evaluate_condition
 *     source: source_value
 *     condition: and
 *     configuration:
 *       -
 *         condition: greater_than
 *         configuration:
 *           value: 4
 *       -
 *         condition: less_than
 *         configuration:
 *           value: 6
 * @endcode
 *
 * For an example of negating the AND-ed conditions, the following is
 * equivalent.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: evaluate_condition
 *     source: source_value
 *     condition: and
 *     configuration:
 *       -
 *         condition: less_than
 *         configuration:
 *           value: 6
 *       -
 *         condition: less_than
 *         negate: true
 *         configuration:
 *           value: 5
 * @endcode
 *
 * Obviously this can be done more clearly using the 'equals' condition.
 * It's just an example.
 *
 * @see \Drupal\migrate\Plugin\MigrateConditionInterface
 *
 * @MigrateConditionPlugin(
 *   id = "and"
 * )
 */
class AndCondition extends LogicalConditionBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate($source, Row $row) {
    foreach ($this->conditions as $index => $condition) {
      if (!($condition->evaluate($source, $row) xor $this->configuration[$index]['negate'])) {
        return FALSE;
      }
    }
    return TRUE;
  }

}
