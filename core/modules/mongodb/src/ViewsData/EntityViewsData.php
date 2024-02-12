<?php

namespace Drupal\mongodb\ViewsData;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Sql\TableMappingInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\views\EntityViewsData as CoreEntityViewsData;

/**
 * Provides generic views integration for entities.
 */
class EntityViewsData extends CoreEntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = [];

    $base_table = $this->entityType->getBaseTable() ?: $this->entityType->id();
    $revisionable = $this->entityType->isRevisionable();
    $translatable = $this->entityType->isTranslatable();
    $base_field = $this->entityType->getKey('id');
    $revision_field = $this->entityType->getKey('revision');

    // Setup base information of the views data.
    $data[$base_table]['table']['group'] = $this->entityType->getLabel();
    $data[$base_table]['table']['provider'] = $this->entityType->getProvider();

    $all_revisions_table = '';
    $current_revision_table = '';
    if ($revisionable) {
      $all_revisions_table = $this->storage->getAllRevisionsTable();
      $data[$base_table]['table']['all revisions table'] = $all_revisions_table;

      $current_revision_table = $this->storage->getCurrentRevisionTable();
      $data[$base_table]['table']['current revision table'] = $current_revision_table;

      $data[$base_table]['table']['entity revision field'] = $revision_field;
    }
    else {
      $data[$base_table]['table']['all revisions table'] = FALSE;
      $data[$base_table]['table']['current revision table'] = FALSE;
      $data[$base_table]['table']['entity revision field'] = FALSE;
    }

    $translations_table = '';
    if ($translatable && !$revisionable) {
      $translations_table = $this->storage->getTranslationsTable();
      $data[$base_table]['table']['translations table'] = $translations_table;
    }
    else {
      $data[$base_table]['table']['translations table'] = FALSE;
    }

    $data[$base_table]['table']['base'] = [
      'field' => $base_field,
      'title' => $this->entityType->getLabel(),
      'cache_contexts' => $this->entityType->getListCacheContexts(),
      'access query tag' => $this->entityType->id() . '_access',
    ];
    $data[$base_table]['table']['entity revision'] = $revisionable;

    if ($label_key = $this->entityType->getKey('label')) {
      $data[$base_table]['table']['base']['defaults'] = [
        'field' => $label_key,
      ];
    }

    if ($this->entityType->hasListBuilderClass()) {
      $data[$base_table]['operations'] = [
        'field' => [
          'title' => $this->t('Operations links'),
          'help' => $this->t('Provides links to perform entity operations.'),
          'id' => 'entity_operations',
        ],
      ];
    }

    if ($this->entityType->hasViewBuilderClass()) {
      $data[$base_table]['rendered_entity'] = [
        'field' => [
          'title' => $this->t('Rendered entity'),
          'help' => $this->t('Renders an entity in a view mode.'),
          'id' => 'rendered_entity',
        ],
      ];
    }

    if ($revisionable) {
      $data[$base_table]['latest_revision'] = [
        'title' => $this->t('Is Latest Revision'),
        'help' => $this->t('Restrict the view to only revisions that are the latest revision of their entity.'),
        'filter' => ['id' => 'latest_revision'],
      ];
      if ($translatable) {
        $data[$base_table]['latest_translation_affected_revision'] = [
          'title' => $this->t('Is Latest Translation Affected Revision'),
          'help' => $this->t('Restrict the view to only revisions that are the latest translation affected revision of their entity.'),
          'filter' => ['id' => 'latest_translation_affected_revision'],
        ];
      }
    }

    $this->addEntityLinks($data[$base_table]);

    $field_definitions = $this->entityFieldManager->getBaseFieldDefinitions($this->entityType->id());

    if ($table_mapping = $this->storage->getTableMapping($field_definitions)) {
      $entity_keys = $this->entityType->getKeys();
      $duplicate_fields = array_intersect_key($entity_keys, array_flip(['id', 'bundle']));

      foreach ($table_mapping->getTableNames() as $table) {
        foreach ($table_mapping->getFieldNames($table) as $field_name) {
          if (($table === $current_revision_table) && in_array($field_name, $duplicate_fields)) {
            continue;
          }
          if ($table === $all_revisions_table) {
            continue;
          }
          $this->mapFieldDefinition($table, $field_name, $field_definitions[$field_name], $table_mapping, $data[$base_table]);
        }
      }
    }

    $entity_type_id = $this->entityType->id();
    array_walk($data, function (&$table_data) use ($entity_type_id) {
      $table_data['table']['entity type'] = $entity_type_id;
    });

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  protected function mapFieldDefinition($table, $field_name, FieldDefinitionInterface $field_definition, TableMappingInterface $table_mapping, &$table_data) {
    $base_table = $this->entityType->getBaseTable() ?: $this->entityType->id();
    $field_column_mapping = $table_mapping->getColumnNames($field_name);
    $field_schema = $this->getFieldStorageDefinitions()[$field_name]->getSchema();
    $field_definition_type = $field_definition->getType();

    // $multiple = (count($field_column_mapping) > 1);
    $first = TRUE;
    foreach ($field_column_mapping as $field_column_name => $schema_field_name) {
      // $views_field_name = ($multiple) ? $field_name . '__' . $field_column_name : $field_name;
      // $table_data[$views_field_name] = $this->mapSingleFieldViewsData($table, $field_name, $field_definition_type, $field_column_name, $field_schema['columns'][$field_column_name]['type'], $first, $field_definition);
      // $table_data[$views_field_name]['entity field'] = $field_name;

      $table_data[$schema_field_name] = $this->mapSingleFieldViewsData($table, $field_name, $field_definition_type, $field_column_name, $field_schema['columns'][$field_column_name]['type'], $first, $field_definition);
      $table_data[$schema_field_name]['entity field'] = $field_name;
      $first = FALSE;

      if ($table != $base_table) {
        if ($table_mapping->requiresDedicatedTableStorage($field_definition->getFieldStorageDefinition())) {
          if ($this->entityType->isRevisionable()) {
            // $table_data[$views_field_name]['real field'] = $this->storage->getCurrentRevisionTable() . '.' . $table . '.' . $views_field_name;
            $table_data[$schema_field_name]['real field'] = $this->storage->getCurrentRevisionTable() . '.' . $table . '.' . $schema_field_name;
          }
          elseif ($this->entityType->isTranslatable()) {
            // $table_data[$views_field_name]['real field'] = $this->storage->getTranslationsTable() . '.' . $table . '.' . $views_field_name;
            $table_data[$schema_field_name]['real field'] = $this->storage->getTranslationsTable() . '.' . $table . '.' . $schema_field_name;
          }
          else {
            // $table_data[$views_field_name]['real field'] = $table . '.' . $views_field_name;
            $table_data[$schema_field_name]['real field'] = $table . '.' . $schema_field_name;
          }
        }
        elseif ($field_name != $this->entityType->getKey('id')) {
          if ($this->entityType->isRevisionable()) {
            // $table_data[$views_field_name]['real field'] = $this->storage->getCurrentRevisionTable() . '.' . $views_field_name;
            $table_data[$schema_field_name]['real field'] = $this->storage->getCurrentRevisionTable() . '.' . $schema_field_name;
          }
          elseif ($this->entityType->isTranslatable()) {
            // $table_data[$views_field_name]['real field'] = $this->storage->getTranslationsTable() . '.' . $views_field_name;
            $table_data[$schema_field_name]['real field'] = $this->storage->getTranslationsTable() . '.' . $schema_field_name;
          }
          else {
            // $table_data[$views_field_name]['real field'] = $views_field_name;
            $table_data[$schema_field_name]['real field'] = $schema_field_name;
          }
        }
        else {
          // $table_data[$views_field_name]['real field'] = $views_field_name;
          $table_data[$schema_field_name]['real field'] = $schema_field_name;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getViewsTableForEntityType(EntityTypeInterface $entity_type) {
    // For MongoDB this is always the entity base table.
    return $entity_type->getBaseTable();
  }

}
