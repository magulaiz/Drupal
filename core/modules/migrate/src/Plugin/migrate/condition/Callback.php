<?php

namespace Drupal\migrate\Plugin\migrate\condition;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Row;

/**
 * Provides a callback condition.
 *
 * Available configuration keys:
 * - callable: The name of the callable method.
 * - unpack_source: (optional) Whether to interpret the source as an array of
 *   arguments.
 *
 * Examples:
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: callback
 *     configuration:
 *       callable: is_null
 *     source: source_field
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateConditionInterface
 *
 * @MigrateConditionPlugin(
 *   id = "callback",
 *   requires = {"callable"}
 * )
 */
class Callback extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if (!is_callable($configuration['callable'])) {
      throw new \InvalidArgumentException('The "callable" must be a valid function or method.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate($source, Row $row) {
    if (!empty($this->configuration['unpack_source'])) {
      if (!is_array($source)) {
        throw new MigrateException(sprintf("When 'unpack_source' is set, the source must be an array. Instead it was of type '%s'", gettype($source)));
      }
      return (bool) call_user_func($this->configuration['callable'], ...$source);
    }
    return (bool) call_user_func($this->configuration['callable'], $source);
  }

}
