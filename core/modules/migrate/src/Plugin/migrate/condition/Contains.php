<?php

namespace Drupal\migrate\Plugin\migrate\condition;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Row;

/**
 * Provides contains condition.
 *
 * This condition can be used on a source array or a source string. If the
 * source is a string, the value/property must also be a string.
 *
 * Available configuration keys:
 * - value: (one of value or property is required) The literal value we search
 *   for in the source.
 * - property: (one of value or property is required) The source or destination
 *   property key to 'get' and then search for in the source.
 *
 * Examples:
 *
 * Skip row if the source_name does not contain 'Dr.'
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: contains
 *     negate: TRUE
 *     method: row
 *     configuration:
 *       value: 'Dr.'
 *     source: source_name
 * @endcode
 *
 * Skip process if source_array contains source_value.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: contains
 *     method: process
 *     configuration:
 *       property: source_value
 *     source: source_array
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateConditionInterface
 *
 * @MigrateConditionPlugin(
 *   id = "contains"
 * )
 */
class Contains extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    // Exactly one of value property and must be set.
    if (array_key_exists('value', $configuration) && isset($configuration['property'])) {
      throw new \InvalidArgumentException("Exactly one of value and property must be set when using the $plugin_id condition.");
    }
    if (isset($configuration['property'])) {
      if (!is_string($configuration['property'])) {
        throw new \InvalidArgumentException("The property configuration must be a string when using the $plugin_id condition.");
      }
    }
    elseif (!array_key_exists('value', $configuration)) {
      // This means that neither value nor property is set.
      throw new \InvalidArgumentException("Exactly one of value and property must be set when using the $plugin_id condition.");
    }
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate($source, Row $row) {
    if (isset($this->configuration['property'])) {
      $value = $row->get($this->configuration['property']);
    }
    else {
      $value = $this->configuration['value'];
    }

    if (is_array($source)) {
      return in_array($value, $source, TRUE);
    }
    elseif (is_string($source)) {
      if (is_string($value)) {
        return str_contains($source, $value);
      }
      else {
        throw new MigrateException('When using the contains condition with a string source, the value/property must be a string.');
      }
    }
    else {
      throw new MigrateException('When using the contains condition the source must be an array or a string.');
    }
  }

}
