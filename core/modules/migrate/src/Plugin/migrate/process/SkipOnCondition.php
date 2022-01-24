<?php

namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipRowException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Skips processing the current row when the input value matches a condition.
 *
 * Available configuration keys:
 * - condition: (required) The id of a MigrateProcessCondition plugin.
 * - configuration: (optional) Additional configuration to be passed to the
 *   condition plugin. Some condition plugins have required configuration.
 * - negate: (optional) A boolean flag that indicates whether condition result
 *   should be negated. Defaults to FALSE.
 * - method: (optional) What to do if the input value is empty. Possible values:
 *   - row: Skips the entire row when an empty value is encountered.
 *   - process: Prevents further processing of the input property when the value
 *     is empty.
 * - message: (optional) A message to be logged in the {migrate_message_*} table
 *   for this row. Messages are only logged for the 'row' method. If not set,
 *   nothing is logged in the message table.
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "skip_on_condition",
 *   handle_multiples = TRUE
 * )
 */
class SkipOnCondition extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The process_condition plugin.
   *
   * @var \Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface
   */
  protected $condition;

  /**
   * Constructs a SkipOnCondition object.
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
   * Skips the current row when value is not set.
   *
   * @param mixed $value
   *   The input value.
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   *   The migration in which this process is being executed.
   * @param \Drupal\migrate\Row $row
   *   The row from the source to process.
   * @param string $destination_property
   *   The destination property currently worked on. This is only used together
   *   with the $row above.
   *
   * @return mixed
   *   The input value, $value, if it is not empty.
   *
   * @throws \Drupal\migrate\MigrateSkipRowException
   *   Thrown if the source property is not set and the row should be skipped,
   *   records with STATUS_IGNORED status in the map.
   */
  public function row($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if ($this->condition->evaluate($value, $row) xor $this->configuration['negate']) {
      $message = !empty($this->configuration['message']) ? $this->configuration['message'] : '';
      throw new MigrateSkipRowException($message);
    }
    return $value;
  }

  /**
   * Stops processing the current property when value is not set.
   *
   * @param mixed $value
   *   The input value.
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   *   The migration in which this process is being executed.
   * @param \Drupal\migrate\Row $row
   *   The row from the source to process.
   * @param string $destination_property
   *   The destination property currently worked on. This is only used together
   *   with the $row above.
   *
   * @return mixed
   *   The input value, $value, if it is not empty.
   *
   * @throws \Drupal\migrate\MigrateSkipProcessException
   *   Thrown if the source property is not set and rest of the process should
   *   be skipped.
   */
  public function process($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if ($this->condition->evaluate($value, $row) xor $this->configuration['negate']) {
      throw new MigrateSkipProcessException();
    }
    return $value;
  }

}
