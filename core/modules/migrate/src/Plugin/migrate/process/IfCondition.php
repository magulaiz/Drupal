<?php

namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Get configured properties based on result of a condition.
 *
 * Available configuration keys:
 * - condition: The id of a MigrateProcessCondition plugin.
 * - configuration: (optional) Additional configuration to be passed to the
 *   condition plugin. Some condition plugins have required configuration.
 * - negate: (optional) A boolean flag that indicates whether condition result
 *   should be negated. Defaults to FALSE.
 * - do_get: (optional) property to get and return if condition is met. If not
 *   set, the source will be used.
 * - else_get: (optional) property to get and return if condition is not met. If
 *   not set, NULL will be returned.
 *
 * Examples:
 *
 * If animal_family is 'bird', get feather_color. Otherwise, get
 * fur_color.
 *
 * @code
 * process:
 *   animal_color:
 *     plugin: if_condition
 *     condition: equals
 *     source: animal_family
 *     configuration:
 *       value: 'bird'
 *     do_get: feather_color
 *     else_get: fur_color
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "if_condition",
 *   handle_multiples = TRUE
 * )
 */
class IfCondition extends ProcessPluginWithConditionBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if ($this->condition->evaluate($value, $row) xor $this->configuration['negate']) {
      if (isset($this->configuration['do_get'])) {
        return $row->get($this->configuration['do_get']);
      }
      else {
        return $value;
      }
    }
    else {
      if (isset($this->configuration['else_get'])) {
        return $row->get($this->configuration['else_get']);
      }
      else {
        return NULL;
      }
    }
  }

}
