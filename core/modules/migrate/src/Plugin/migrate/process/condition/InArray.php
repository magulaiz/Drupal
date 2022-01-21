<?php

namespace Drupal\migrate\Plugin\migrate\process\condition;

/**
 * Provides in_array condition.
 *
 * Available configuration keys:
 * - array: The haystack array.
 * - strict: (optional) 'strict' parameter for in_array. Defaults to FALSE.
 *
 * Examples:
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: in_array
 *     configuration:
 *       array:
 *         - one
 *         - two
 *         - three
 *       strict: TRUE
 *     source: source_field
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface
 *
 * @MigrateProcessConditionPlugin(
 *   id = "in_array",
 *   requires = {"array"}
 * )
 */
class InArray extends ProcessConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate($source) {
    return in_array($source, (array) $this->configuration['array'], $this->configuration['strict'] ?? FALSE);
  }

}
