<?php

namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Evaluates a condition.
 *
 * Available configuration keys:
 * - condition: The id of a MigrateProcessCondition plugin.
 * - configuration: (optional) Additional configuration to be passed to the
 *   condition plugin. Some condition plugins have required configuration.
 * - negate: (optional) A boolean flag that indicates whether condition result
 *   should be negated. Defaults to FALSE.
 *
 * EXamples:
 *
 * Set as unpublished if event_date is in the past.
 *
 * @code
 * process:
 *   status:
 *     plugin: evaluate_condition
 *     condition: older_than
 *     configuration:
 *       format: 'U'
 *       value: 'now'
 *     source: event_date
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "evaluate_condition",
 *   handle_multiples = TRUE
 * )
 */
class EvaluateCondition extends ProcessPluginWithConditionBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return $this->condition->evaluate($value, $row) xor $this->configuration['negate'];
  }

}
