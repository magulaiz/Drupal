<?php

namespace Drupal\migrate\Plugin\migrate\process\condition;

use Drupal\migrate\MigrateException;

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
 * @see \Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface
 *
 * @MigrateProcessConditionPlugin(
 *   id = "callback",
 *   requires = {"callable"}
 * )
 */
class Callback extends ProcessConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    if (!isset($configuration['callable'])) {
      throw new \InvalidArgumentException('The "callable" must be set.');
    }
    elseif (!is_callable($configuration['callable'])) {
      throw new \InvalidArgumentException('The "callable" must be a valid function or method.');
    }
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate($source) {
    if (!empty($this->configuration['unpack_source'])) {
      if (!is_array($source)) {
        throw new MigrateException(sprintf("When 'unpack_source' is set, the source must be an array. Instead it was of type '%s'", gettype($value)));
      }
      return (bool) call_user_func($this->configuration['callable'], ...$source);
    }
    return (bool) call_user_func($this->configuration['callable'], $source);
  }

}
