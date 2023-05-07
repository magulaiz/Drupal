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
 * - strict: (optional) If set to TRUE, the callback is considered false only
 *   if it is identically equal to false. Defaults to FALSE. This is useful for
 *   callbacks like strpos, which may return a 0 that does not indicate FALSE.
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
    $this->configuration['strict'] = $configuration['strict'] ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate($source, Row $row) {
    if (!empty($this->configuration['unpack_source'])) {
      if (!is_array($source)) {
        throw new MigrateException(sprintf("When 'unpack_source' is set, the source must be an array. Instead it was of type '%s'", gettype($source)));
      }
      if ($this->configuration['strict']) {
        return call_user_func($this->configuration['callable'], ...$source) !== FALSE;
      }
      else {
        return (bool) call_user_func($this->configuration['callable'], ...$source);
      }
    }
    if ($this->configuration['strict']) {
      return call_user_func($this->configuration['callable'], $source) !== FALSE;
    }
    else {
      return (bool) call_user_func($this->configuration['callable'], $source);
    }
  }

}
