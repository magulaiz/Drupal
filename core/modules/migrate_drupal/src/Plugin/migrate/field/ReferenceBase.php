<?php

namespace Drupal\migrate_drupal\Plugin\migrate\field;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\MigratePluginManager;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Drupal reference fields.
 */
abstract class ReferenceBase extends FieldPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The migration plugin manager.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface
   */
  protected $migrationPluginManager;

  /**
   * The migrate process plugin manager.
   *
   * @var \Drupal\migrate\Plugin\MigratePluginManager
   */
  protected $migrateProcessPluginManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationPluginManagerInterface $migration_plugin_manager, MigratePluginManager $migrate_process_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migrationPluginManager = $migration_plugin_manager;
    $this->migrateProcessPluginManager = $migrate_process_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.migration'),
      $container->get('plugin.manager.migrate.process')
    );
  }

  /**
   * Gets the plugin ID for the reference type migration.
   *
   * The reference type migration will be added as a required dependency.
   *
   * @return string
   *   The plugin id.
   */
  abstract protected function getEntityTypeMigrationId();

  /**
   * Gets the name of the field property which holds the entity ID.
   *
   * @return string
   *   The entity id.
   */
  abstract protected function entityId();

  /**
   * {@inheritdoc}
   */
  public function alterFieldInstanceMigration(MigrationInterface $migration) {
    parent::alterFieldInstanceMigration($migration);

    // Add the reference migration as a required dependency to this migration.
    $migration_dependencies = $migration->getMigrationDependencies();
    array_push($migration_dependencies['required'], $this->getEntityTypeMigrationId());
    $migration_dependencies['required'] = array_unique($migration_dependencies['required']);
    $migration->set('migration_dependencies', $migration_dependencies);
  }

  /**
   * {@inheritdoc}
   */
  public function defineValueProcessPipeline(MigrationInterface $migration, $field_name, $data) {
    $process = [
      'plugin' => 'sub_process',
      'source' => $field_name,
      'process' => ['target_id' => $this->entityId()],
    ];
    $migration->setProcessOfProperty($field_name, $process);
  }

  /**
   * Look up migrated role IDs from a migration.
   *
   * @param string $migration_id
   *   The migration ID which migrated the source.
   * @param array $source_ids
   *   The source IDs.
   *
   * @return array
   *   The migrated IDs.
   */
  protected function lookupMigrations($migration_id, array $source_ids) {
    // Configure the migration process plugin to look up migrated IDs from
    // the migration.
    $migration_plugin_configuration = [
      'migration' => $migration_id,
    ];

    $row = new Row([], []);

    /** @var \Drupal\migrate\Plugin\MigrationInterface $migration */
    $migration = $this->migrationPluginManager->createStubMigration([]);

    /** @var \Drupal\migrate\Plugin\MigrateProcessInterface $migrationProcessPlugin */
    $migrationProcessPlugin = $this->migrateProcessPluginManager
      ->createInstance('migration_lookup', $migration_plugin_configuration, $migration);

    $executable = new MigrateExecutable($migration, new MigrateMessage());

    $ids = [];
    foreach ($source_ids as $source_id) {
      $ids[] = $migrationProcessPlugin->transform($source_id, $executable, $row, NULL);
    }
    return array_combine($ids, $ids);
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldFormatterMap() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldWidgetMap() {
    return [
      $this->pluginId . '_select' => 'options_select',
      $this->pluginId . '_buttons' => 'options_buttons',
      $this->pluginId . '_autocomplete' => 'entity_reference_autocomplete_tags',
    ];
  }

}
