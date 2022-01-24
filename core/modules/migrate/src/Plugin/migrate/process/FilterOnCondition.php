<?php

namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filters an input array on a condition.
 *
 * Available configuration keys:
 * - condition: (required) The id of a MigrateProcessCondition plugin.
 * - configuration: (optional) Additional configuration to be passed to the
 *   condition plugin. Some condition plugins have required configuration.
 * - negate: (optional) A boolean flag that indicates whether condition result
 *   should be negated. Defaults to FALSE.
 * - preserve_keys: (optional) Set to TRUE to preserve array keys. Defaults to
 *   FALSE.
 *
 * Examples:
 *
 * Remove any tags that don't exist.
 *
 * @code
 * process:
 *   destination_property:
 *     plugin: filter_on_condition
 *     condition: entity_exists
 *     negate: TRUE
 *     configuration:
 *       entity_id: 'taxonomy_term'
 *     source: field_tag_ids
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "filter_on_condition",
 *   handle_multiples = TRUE
 * )
 */
class FilterOnCondition extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The process_condition plugin.
   *
   * @var \Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface
   */
  protected $condition;

  /**
   * Constructs a MenuLinkParent object.
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
    if (!is_array($value)) {
      throw new MigrateException("The input value should be an array.");
    }
    $return = [];
    foreach ($value as $key => $val) {
      if ($this->condition->evaluate($val, $row) xor $this->configuration['negate']) {
        $return[$key] = $val;
      }
    }
    if (isset($this->configuration['preserve_keys']) && $this->configuration['preserve_keys']) {
      return $return;
    }
    else {
      return array_values($return);
    }
  }

}
