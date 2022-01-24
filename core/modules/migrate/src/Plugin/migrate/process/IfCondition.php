<?php

namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Get configured properties based on result of a condition.
 *
 * Available configuration keys:
 * - condition: (required) The id of a MigrateProcessCondition plugin.
 * - configuration: (optional) Additional configuration to be passed to the
 *   condition plugin. Some condition plugins have required configuration.
 * - negate: (optional) A boolean flag that indicates whether condition result
 *   should be negated. Defaults to FALSE.
 * - do_get: (optional) property to get and return if condition is met. If not
 *   set, the source will be used.
 * - else_get: (optional) property to get and return if condition is not met. If
 *   not set, NULL will be returned.
 *
 * Examples:
 *
 * If animal_family is 'bird', get feather_color. Otherwise, get
 * fur_color.
 *
 * @code
 * process:
 *   animal_color:
 *     plugin: if_condition
 *     condition: equals
 *     source: animal_family
 *     configuration:
 *       value: 'bird'
 *     do_get: feather_color
 *     else_get: fur_color
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "if_condition",
 *   handle_multiples = TRUE
 * )
 */
class IfCondition extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The process_condition plugin.
   *
   * @var \Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface
   */
  protected $condition;

  /**
   * Constructs an EvaluateCondition object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $process_condition_manager
   *   The MigrateProcessCondition plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PluginManagerInterface $process_condition_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if (!isset($configuration['condition'])) {
      throw new \InvalidArgumentException('The "condition" must be set.');
    }
    $this->condition = $process_condition_manager->createInstance($configuration['condition'], $configuration['configuration'] ?? []);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.migrate.process_condition')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if ($this->condition->evaluate($value, $row) xor $this->configuration['negate']) {
      if (isset($this->configuration['do_get'])) {
        return $row->get($this->configuration['do_get']);
      }
      else {
        return $value;
      }
    }
    else {
      if (isset($this->configuration['else_get'])) {
        return $row->get($this->configuration['else_get']);
      }
      else {
        return NULL;
      }
    }
  }

}
