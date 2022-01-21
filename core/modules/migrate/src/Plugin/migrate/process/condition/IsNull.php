<?php

namespace Drupal\migrate\Plugin\migrate\process\condition;

/**
 * Provides is_null() condition.
 *
 * Examples:
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: is_null
 *     source: source_field
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface
 *
 * @MigrateProcessConditionPlugin(
 *   id = "is_null"
 * )
 */
class IsNull extends ProcessConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate($source) {
    return is_null($source);
  }

}
