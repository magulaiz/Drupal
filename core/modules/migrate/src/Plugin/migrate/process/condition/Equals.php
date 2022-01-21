<?php

namespace Drupal\migrate\Plugin\migrate\process\condition;

/**
 * Provides an equals condition.
 *
 * Available configuration keys:
 * - value: The value(s) to compare with.
 * - identical: (optional) Pass TRUE to compare with ===.
 *
 * Examples:
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: equals
 *     configuration:
 *       value: 5
 *       identical: FALSE
 *     source: source_field
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface
 *
 * @MigrateProcessConditionPlugin(
 *   id = "equals",
 *   requires = {"value"}
 * )
 */
class Equals extends ProcessConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate($source) {
    if (isset($this->configuration['identical']) && $this->configuration['identical']) {
      return $source === $this->configuration['value'];
    }
    else {
      return $source == $this->configuration['value'];
    }
  }

}
