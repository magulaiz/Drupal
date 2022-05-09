<?php

namespace Drupal\migrate_drupal\Plugin\migrate\field\d6;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\field\ReferenceBase;

/**
 * MigrateField Plugin for Drupal 6 user reference fields.
 *
 * @MigrateField(
 *   id = "userreference",
 *   core = {6},
 *   type_map = {
 *     "userreference" = "entity_reference",
 *   },
 *   source_module = "userreference",
 *   destination_module = "core",
 * )
 *
 * @internal
 */
class UserReference extends ReferenceBase {

  /**
   * The plugin ID for the reference type migration.
   *
   * @var string
   */
  protected $userTypeMigration = 'd6_user_role';

  /**
   * The plugin ID for the user role migration.
   *
   * @var string
   */
  protected $userRoleMigration = 'd6_user_role';

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeMigrationId() {
    return $this->userTypeMigration;
  }

  /**
   * {@inheritdoc}
   */
  protected function entityId() {
    return 'uid';
  }

  /**
   * {@inheritdoc}
   */
  public function defineValueProcessPipeline(MigrationInterface $migration, $field_name, $data) {
    $process = [
      'plugin' => 'sub_process',
      'source' => $field_name,
      'process' => [
        'target_id' => [
          'plugin' => 'migration_lookup',
          'migration' => 'd6_user',
          'source' => 'uid',
        ],
      ],
    ];
    $migration->setProcessOfProperty($field_name, $process);
  }

  /**
   * {@inheritdoc}
   */
  public function transformFieldStorageSettings(Row $row) {
    $settings['target_type'] = 'user';
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function alterFieldInstanceMigration(MigrationInterface $migration) {
    parent::alterFieldInstanceMigration($migration);

    $migration_dependencies = $migration->getMigrationDependencies();
    $migration_dependencies['required'][] = $this->userRoleMigration;
    $migration->set('migration_dependencies', $migration_dependencies);
  }

  /**
   * {@inheritdoc}
   */
  public function transformFieldInstanceSettings(Row $row) {
    $source_settings = $row->getSourceProperty('global_settings');
    $settings['handler'] = 'default:user';
    $settings['handler_settings']['include_anonymous'] = FALSE;
    $settings['handler_settings']['filter']['type'] = '_none';
    $settings['handler_settings']['target_bundles'] = NULL;

    if (isset($source_settings['referenceable_roles'])) {
      $roles = array_filter($source_settings['referenceable_roles']);
      if (!empty($roles)) {
        $settings['handler_settings']['filter']['type'] = 'role';
        $settings['handler_settings']['filter']['role'] = $this->lookupMigrations($this->userRoleMigration, $roles);
      }
    }
    return $settings;
  }

}
