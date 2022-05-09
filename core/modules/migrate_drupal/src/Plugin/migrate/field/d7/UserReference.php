<?php

namespace Drupal\migrate_drupal\Plugin\migrate\field\d7;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\field\ReferenceBase;

/**
 * MigrateField plugin for Drupal 7 user_reference fields.
 *
 * @MigrateField(
 *   id = "user_reference",
 *   type_map = {
 *     "user_reference" = "entity_reference",
 *   },
 *   core = {7},
 *   source_module = "user_reference",
 *   destination_module = "core"
 * )
 */
class UserReference extends ReferenceBase {

  /**
   * The plugin ID for the reference type migration.
   *
   * @var string
   */
  protected $userTypeMigration = 'd7_user_role';

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
  public function getFieldFormatterMap() {
    return [
      'user_reference_default' => 'entity_reference_label',
      'user_reference_plain' => 'entity_reference_label',
      'user_reference_uid' => 'entity_reference_entity_id',
      'user_reference_user' => 'entity_reference_entity_view',
      'user_reference_path' => 'entity_reference_label',
    ];
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
          'migration' => 'd7_user',
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
    $migration_dependencies['required'][] = $this->userTypeMigration;
    $migration->set('migration_dependencies', $migration_dependencies);
  }

  /**
   * {@inheritdoc}
   */
  public function transformFieldInstanceSettings(Row $row) {
    $instance_settings['handler'] = 'default:user';

    $instance_settings['handler_settings'] = [
      'include_anonymous' => TRUE,
      'filter' => [
        'type' => '_none',
      ],
      'sort' => [
        'field' => '_none',
        'direction' => 'ASC',
      ],
      'auto_create' => FALSE,
    ];

    if ($row->hasSourceProperty('roles')) {
      $instance_settings['handler_settings']['filter']['type'] = 'role';
      foreach ($row->get('roles') as $role) {
        $instance_settings['handler_settings']['filter']['role'] = [
          $role['name'] => $role['name'],
        ];
      }
    }
    return $instance_settings;
  }

}
