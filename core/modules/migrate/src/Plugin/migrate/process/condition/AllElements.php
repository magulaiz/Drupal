<?php

namespace Drupal\migrate\Plugin\migrate\process\condition;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an all_elements condition.
 *
 * Evaluates the configured condition on each array element and returns
 * TRUE if each element meets the condition.
 *
 * Available configuration keys:
 * - condition: The condition plugin to evaluate on each element.
 * - negate: (optional) Whether to negate the configured condition.
 *   Defaults to FALSE.
 * - configuration: (optional) Configuration to pass to the configured
 *   condition.
 *
 * Example:
 *
 * Skip a row if every date in the array source_dates is too old.
 *
 * skip:
 *   plugin: skip_on_condition
 *   condition: all_elements
 *   configuration:
 *     condition: older_than
 *     format: 'U'
 *     value: -1 week'
 *   method: row
 *   source: source_dates
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface
 *
 * @MigrateProcessConditionPlugin(
 *   id = "all_elements",
 *   requires = {"condition"}
 * )
 */
class AllElements extends ProcessConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The process_condition plugin.
   *
   * @var \Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface
   */
  protected $condition;

  /**
   * Constructs an AllElements object.
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
    $this->configuration['negate'] = $this->configuration['negate'] ?? FALSE;
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
  public function evaluate($source, Row $row) {
    foreach ((array) $source as $source_value) {
      if (!($this->condition->evaluate($source_value, $row) xor $this->configuration['negate'])) {
        return FALSE;
      }
    }
    return TRUE;
  }

}
