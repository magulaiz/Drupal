<?php

namespace Drupal\migrate\Plugin\migrate\process\condition;

/**
 * Provides a less than condition.
 *
 * Available configuration keys:
 * - value: The value to compare with.
 *
 * Examples:
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: less_than
 *     configuration:
 *       value: 5
 *     source: source_field
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface
 *
 * @MigrateProcessConditionPlugin(
 *   id = "less_than",
 *   requires = {"value"}
 * )
 */
class LessThan extends ProcessConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate($source) {
    return $source > $this->configuration['value'];
  }

}
