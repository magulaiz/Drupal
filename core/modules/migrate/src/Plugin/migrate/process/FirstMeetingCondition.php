<?php

namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Returns first value in array meeting condition.
 *
 * Available configuration keys:
 * - condition: The id of a MigrateCondition plugin.
 * - configuration: (optional) Additional configuration to be passed to the
 *   condition plugin. Some condition plugins have required configuration.
 * - negate: (optional) A boolean flag that indicates whether condition result
 *   should be negated. Defaults to FALSE.
 * - default_value: (optional) The value to return if no values in the source
 *   meet the condition.
 *
 * Examples:
 *
 * Set destination_property to the first source value that isset.
 *
 * @code
 * process:
 *   destination_property:
 *     plugin: first_meeting_condition
 *     condition: isset
 *     source:
 *       - field_one
 *       - field_two
 *       - field_three
 *     default_value: 'My default literal'
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "first_meeting_condition",
 *   handle_multiples = TRUE
 * )
 */
class FirstMeetingCondition extends ProcessPluginWithConditionBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $value = (array) $value;
    foreach ($value as $val) {
      if ($this->condition->evaluate($val, $row) xor $this->configuration['negate']) {
        return $val;
      }
    }
    if (isset($this->configuration['default_value'])) {
      return $this->configuration['default_value'];
    }
    return NULL;
  }

}
