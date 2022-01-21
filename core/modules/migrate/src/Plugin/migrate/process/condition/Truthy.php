<?php

namespace Drupal\migrate\Plugin\migrate\process\condition;

/**
 * Provides a truthy condition.
 *
 * Available configuration keys:
 *
 * Examples:
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: truthy
 *     source: source_field
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface
 *
 * @MigrateProcessConditionPlugin(
 *   id = "truthy"
 * )
 */
class Truthy extends ProcessConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate($source) {
    return (bool) $source;
  }

}
