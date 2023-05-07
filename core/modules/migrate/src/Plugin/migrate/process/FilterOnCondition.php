<?php

namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Filters an input array on a condition.
 *
 * Available configuration keys:
 * - condition: The id of a MigrateCondition plugin.
 * - configuration: (optional) Additional configuration to be passed to the
 *   condition plugin. Some condition plugins have required configuration.
 * - negate: (optional) A boolean flag that indicates whether condition result
 *   should be negated. Defaults to FALSE.
 * - preserve_keys: (optional) Set to TRUE to preserve array keys. Defaults to
 *   FALSE.
 *
 * Examples:
 *
 * Remove any tags that don't exist.
 *
 * @code
 * process:
 *   destination_property:
 *     plugin: filter_on_condition
 *     condition: entity_exists
 *     negate: TRUE
 *     configuration:
 *       entity_id: 'taxonomy_term'
 *     source: field_tag_ids
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "filter_on_condition",
 *   handle_multiples = TRUE
 * )
 */
class FilterOnCondition extends ProcessPluginWithConditionBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!is_array($value)) {
      throw new MigrateException("The input value should be an array.");
    }
    $return = [];
    foreach ($value as $key => $val) {
      if ($this->condition->evaluate($val, $row) xor $this->configuration['negate']) {
        $return[$key] = $val;
      }
    }
    if (isset($this->configuration['preserve_keys']) && $this->configuration['preserve_keys']) {
      return $return;
    }
    else {
      return array_values($return);
    }
  }

}
