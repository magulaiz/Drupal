<?php

namespace Drupal\migrate\Plugin\migrate\process\condition;

use Drupal\migrate\Row;

/**
 * Provides isset() condition.
 *
 * Examples:
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: isset
 *     source: source_field
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface
 *
 * @MigrateProcessConditionPlugin(
 *   id = "isset"
 * )
 */
class IssetCondition extends ProcessConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate($source, Row $row) {
    return isset($source);
  }

}
