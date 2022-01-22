<?php

namespace Drupal\migrate\Plugin\migrate\process\condition;

use Drupal\migrate\Row;

/**
 * Provides empty() condition.
 *
 * Examples:
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: empty
 *     source: source_field
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface
 *
 * @MigrateProcessConditionPlugin(
 *   id = "empty"
 * )
 */
class EmptyCondition extends ProcessConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate($source, Row $row) {
    return empty($source);
  }

}
