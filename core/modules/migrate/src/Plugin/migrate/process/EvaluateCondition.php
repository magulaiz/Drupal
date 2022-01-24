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
 * Evaluates a condition.
 *
 * Available configuration keys:
 * - condition: (required) The id of a MigrateProcessCondition plugin.
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
class EvaluateCondition extends ProcessPluginBase implements ContainerFactoryPluginInterface {

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
    return $this->condition->evaluate($value, $row) xor $this->configuration['negate'];
  }

}
