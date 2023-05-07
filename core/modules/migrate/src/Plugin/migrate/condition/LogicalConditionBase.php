<?php

namespace Drupal\migrate\Plugin\migrate\condition;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base class for conditions that compare other conditions.
 *
 * Plugins extending this class allow logical combinations of multiple
 * conditions.
 *
 * Plugins extending this class require that configuration must be an array of
 * arrays, each with the following available keys.
 * - condition: The id of a MigrateCondition plugin.
 * - configuration: (optional) Additional configuration to be passed to the
 *   condition plugin. Some condition plugins have required configuration.
 * - negate: (optional) A boolean flag that indicates whether condition result
 *   should be negated. Defaults to FALSE.
 */
abstract class LogicalConditionBase extends ConditionBase implements ContainerFactoryPluginInterface {

  /**
   * The condition plugin array.
   *
   * @var \Drupal\migrate\Plugin\MigrateConditionInterface[]
   */
  protected $conditions;

  /**
   * Constructs a HasElement object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $condition_manager
   *   The MigrateCondition plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PluginManagerInterface $condition_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if (empty($configuration)) {
      throw new \InvalidArgumentException("The $plugin_id condition requires configuration.");
    }
    foreach ($this->configuration as &$config) {
      if (!is_array($config)) {
        throw new \InvalidArgumentException("The 'configuration' passed to the $plugin_id condition must be an array or arrays.");
      }
      if (!isset($config['condition'])) {
        throw new \InvalidArgumentException("Each configuration element passed to the $plugin_id condition must have the `condition` set.");
      }
      $this->conditions[] = $condition_manager->createInstance($config['condition'], $config['configuration'] ?? []);
      $config['negate'] = $config['negate'] ?? FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.migrate.condition')
    );
  }

}
