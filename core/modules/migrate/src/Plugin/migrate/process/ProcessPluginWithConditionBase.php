<?php

namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\ProcessPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class to be used by MigrateProcess plugins that rely on a condition.
 *
 * Available configuration keys:
 * - condition: (required) The id of a MigrateProcessCondition plugin.
 * - configuration: (optional) Additional configuration to be passed to the
 *   condition plugin. Some condition plugins have required configuration.
 * - negate: (optional) A boolean flag that indicates whether condition result
 *   should be negated. Defaults to FALSE.
 */
abstract class ProcessPluginWithConditionBase extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The process_condition plugin.
   *
   * @var \Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface
   */
  protected $condition;

  /**
   * Constructs a ProcessPluginWithConditionBase object.
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
    if (isset($configuration['configuration']) && !is_array($configuration['configuration'])) {
      throw new \InvalidArgumentException('If "configuration" is set it must be an array.');
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

}
