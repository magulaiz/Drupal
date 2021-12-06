<?php

namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Passes the source value to a callback.
 *
 * The callback process plugin allows simple processing of the value, such as
 * strtolower(). To pass more than one argument, pass an array as the source
 * and set the unpack_source option. To pass no arguments, set no_args to true.
 *
 * Available configuration keys:
 * - callable: The name of the callable method.
 * - unpack_source: (optional) Whether to interpret the source as an array of
 *   arguments.
 * - no_args: (optional) Whether to pass no arguments to the callback. To be
 *   used in cases where a function does not accept arguments, such as time().
 *
 *
 * Examples:
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: callback
 *     callable: mb_strtolower
 *     source: source_field
 * @endcode
 *
 * An example where the callable is a static method in a class:
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: callback
 *     callable:
 *       - '\Drupal\Component\Utility\Unicode'
 *       - ucfirst
 *     source: source_field
 * @endcode
 *
 * An example where the callback accepts more than one argument:
 *
 * @code
 * source:
 *   plugin: source_plugin_goes_here
 *   constants:
 *     slash: /
 * process:
 *   field_link_url:
 *     plugin: callback
 *     callable: rtrim
 *     unpack_source: true
 *     source:
 *       - url
 *       - constants/slash
 * @endcode
 *
 * This will remove the trailing '/', if any, from a URL.
 *
 * An example where the callable accepts no arguments:
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: callback
 *     callable: time
 *     no_args: true
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "callback"
 * )
 */
class Callback extends ProcessPluginBase {

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
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!empty($this->configuration['unpack_source'])) {
      if (!is_array($value)) {
        throw new MigrateException(sprintf("When 'unpack_source' is set, the source must be an array. Instead it was of type '%s'", gettype($value)));
      }
      return call_user_func($this->configuration['callable'], ...$value);
    }

    if (!empty($this->configuration['no_args'])) {
      return call_user_func($this->configuration['callable']);
    }

    return call_user_func($this->configuration['callable'], $value);
  }

}
