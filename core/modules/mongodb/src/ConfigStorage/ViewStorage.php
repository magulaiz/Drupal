<?php

namespace Drupal\mongodb\ConfigStorage;

use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mongodb\Service\TranslateViews;

/**
 * The MongoDB implementation for the storage class for view entities.
 */
class ViewStorage extends ConfigEntityStorage {

  /**
   * The MongoDB table information service.
   *
   * @var \Drupal\mongodb\Driver\Database\mongodb\TableInformation
   */
  protected $tableInformation;

  /**
   * Helper method for updating a view from relational to MongoDB.
   *
   * @param array $values
   *   An array with view entity config data.
   *
   * @return array
   *   An array with view entity config data for a MongoDB database.
   */
  protected function updateViewForMongodb($values) {
    // TODO: Remove reverse relationships and associated fields. MongoDB does
    // not need or support them.
    // See: Drupal\Tests\field\Kernel\EntityReference\Views\EntityReferenceRelationshipTest.

    $removed_relationships = [];
    $original_base_table = '';

    // Update the records so that they will work for MongoDB.
    if (!empty($values['base_table'])) {
      $original_base_table = $values['base_table'];
      $base_table = TranslateViews::baseTable($values['base_table']);
      $values['mongodb_base_table'] = $base_table;
      $values['original_base_table'] = $values['base_table'];
      if (!empty($base_table) && ($original_base_table != $base_table)) {
        $entity_type = NULL;
        if (!empty($values['entity_type'])) {
          $entity_type = \Drupal::entityTypeManager()->getDefinition($values['entity_type']);
        }
        elseif ($entity_type = $this->getEntityTypeFromView($values, [$base_table, $original_base_table])) {
          $values['entity_type'] = $entity_type->id();
        }
        if ($entity_type) {
          if ($entity_type->isRevisionable() && in_array($original_base_table, [$entity_type->getRevisionTable(), $entity_type->getRevisionDataTable()], TRUE)) {
            $values['all_revisions_table'] = \Drupal::entityTypeManager()->getStorage($entity_type->id())->getAllRevisionsTable();
            $values['latest_revision_table'] = \Drupal::entityTypeManager()->getStorage($entity_type->id())->getLatestRevisionTable();
            $values['current_revision_table'] = \Drupal::entityTypeManager()->getStorage($entity_type->id())->getCurrentRevisionTable();
          }
          elseif ($entity_type->isRevisionable()) {
            $values['current_revision_table'] = \Drupal::entityTypeManager()->getStorage($entity_type->id())->getCurrentRevisionTable();
          }
          elseif (!empty($entity_type->getDataTable()) && ($original_base_table == $entity_type->getDataTable())) {
            $values['translations_table'] = \Drupal::entityTypeManager()->getStorage($entity_type->id())->getTranslationsTable();
          }
        }
      }
    }
    if (isset($values['display']) && is_array($values['display'])) {
      foreach ($values['display'] as &$display) {
        if (isset($display['display_options']) && is_array($display['display_options'])) {

          // MongoDB stores all entity data in the base table of the entity.
          // Table joins to other entity tables are therefor not necessary.
          foreach ($display['display_options'] as $display_options_id => &$display_options) {
            if ($display_options_id == 'relationships' && is_array($display_options)) {
              foreach ($display_options as $display_option_key => &$display_option) {
                $table = !empty($display_option['table']) ? $display_option['table'] : NULL;
                $entity_type = !empty($display_option['entity_type']) ? $display_option['entity_type'] : NULL;

                // TODO: See if we can solve this more generally.
                if (isset($display_option['table']) && ($display_option['table'] == 'taxonomy_term__parent') &&
                  isset($display_option['field']) && ($display_option['field'] == 'parent_target_id')) {

                  // Remove the relationship.
                  unset($display_options[$display_option_key]);

                  // Add the removed relationship to the list of removed
                  // relationships.
                  $removed_relationships[] = $display_option_key;
                }

                if (isset($display_option['plugin_id']) && ($display_option['plugin_id'] == 'entity_reverse')) {
                  // Remove the relationship.
                  unset($display_options[$display_option_key]);

                  // Add the removed relationship to the list of removed
                  // relationships.
                  $removed_relationships[] = $display_option_key;
                }

                $has_fields = $this->hasFieldsNotFromBaseTable($values, TranslateViews::baseTable($table));
                if (!$has_fields) {
                  // Remove the relationship.
                  unset($display_options[$display_option_key]);

                  // Add the removed relationship to the list of removed
                  // relationships.
                  $removed_relationships[] = $display_option_key;
                }
              }
            }
          }

          // Replace the removed relationships with the relationship "none".
          foreach ($display['display_options'] as $display_options_id => &$display_options) {
            if (in_array($display_options_id, ['fields', 'filters']) && is_array($display_options)) {
              foreach ($display_options as $display_option_key => &$display_option) {
                if (!empty($display_option['relationship']) && in_array($display_option['relationship'], $removed_relationships, TRUE)) {
                  $display_option['relationship'] = 'none';
                }
              }
            }

            // For each field, filter and relationship replace the relational
            // database table name for the MongoDB version.
            if (in_array($display_options_id, ['fields', 'filters', 'sorts', 'arguments', 'relationships']) && is_array($display_options)) {
              foreach ($display_options as $display_option_key => &$display_option) {
                if (!empty($display_option['table']) && !empty($display_option['entity_type'])) {
                  $entity_type = \Drupal::entityTypeManager()->getDefinition($display_option['entity_type']);
                  if ($entity_type instanceof EntityTypeInterface) {
                    if (!empty($display_option['entity_field']) &&
                            is_string($display_option['entity_field'])
                    ) {
                      $field_storage_definitions = \Drupal::service('entity_field.manager')->getFieldStorageDefinitions($entity_type->id());
                      if (in_array($display_option['entity_field'], array_keys($field_storage_definitions), TRUE)) {
                        $field_storage_definition = $field_storage_definitions[$display_option['entity_field']];
                        $storage = \Drupal::entityTypeManager()->getStorage($entity_type->id());
                        $table_mapping = $storage->getTableMapping();
                        if ($table_mapping->requiresDedicatedTableStorage($field_storage_definition)) {
                          $display_option['table'] = $entity_type->getBaseTable();
                        }
                      }
                    }
                    if (!empty($display_option['table']) && in_array($display_option['table'], $this->getEntityTables($entity_type), TRUE)) {
                      if (($display_options_id == 'relationships') && isset($display_option['table']) && TranslateViews::isRevisionTable($display_option['table'])) {
                        $display_option['revisionable_join'] = TRUE;
                      }
                      $display_option['table'] = $entity_type->getBaseTable();
                    }
                  }
                }
                elseif (!empty($display_option['table'])) {
                  $display_option['table'] = TranslateViews::baseTable($display_option['table']);
                }
                if (($display_options_id == 'relationships') && isset($display_option['plugin_id']) && ($display_option['plugin_id'] == 'groupwise_max')) {
                  if (isset($display_option['subquery_sort']) && ($display_option['subquery_sort'] == 'node_field_data.nid')) {
                    $display_option['subquery_sort'] = 'node.nid';
                  }
                }
              }
            }
          }

          if (isset($display['cache_metadata'])) {
            // Both max-age and tags values must be set.
            if (!isset($display['cache_metadata']['max-age'])) {
              $display['cache_metadata']['max-age'] = Cache::PERMANENT;
            }
            if (!isset($display['cache_metadata']['tags'])) {
              $display['cache_metadata']['tags'] = [];
            }
          }
        }
      }
    }

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  protected function mapFromStorageRecords(array $records) {
    // Update the records so that they are ready for the MongoDB backend.
    foreach ($records as &$record) {
      $record = $this->updateViewForMongodb($record);
    }

    return parent::mapFromStorageRecords($records);
  }

  /**
   * Get list of all entity tables for an entity..
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type for which to get the entity tables.
   *
   * @return array
   *   A list of entity tables.
   */
  protected function getEntityTables($entity_type) {
    if ($entity_type instanceof EntityTypeInterface) {
      $storage = \Drupal::entityTypeManager()->getStorage($entity_type->id());
      $entity_tables = [
        $entity_type->getBaseTable(),
        $entity_type->getDataTable(),
        $entity_type->getRevisionTable(),
        $entity_type->getRevisionDataTable(),
        $storage->getAllRevisionsTable(),
        $storage->getCurrentRevisionTable(),
        $storage->getTranslationsTable(),
      ];
      return array_filter($entity_tables);
    }
    return [];
  }

  /**
   * Helper method for getting the entity type belonging to the table.
   *
   * @param array $records
   *   Associative array of query results, keyed on the entity ID.
   * @param string $base_table
   *   The base table name for which to search the fields of view.
   *
   * @return bool
   *   If there are field that do not belong to the given base table.
   */
  protected function hasFieldsNotFromBaseTable(array &$records, $base_table) {
    $has_fields = FALSE;
    if (isset($records['display']) && is_array($records['display'])) {
      foreach ($records['display'] as &$display) {
        if (isset($display['display_options']) && is_array($display['display_options'])) {
          foreach ($display['display_options'] as &$display_options) {
            if (is_array($display_options)) {
              foreach ($display_options as &$display_option) {
                if (is_array($display_option) && !empty($display_option['table']) && ($base_table != TranslateViews::baseTable($display_option['table']))) {
                  $has_fields = TRUE;
                }
              }
            }
          }
        }
      }
    }

    return $has_fields;
  }

  /**
   * Helper method for getting the entity type belonging to the table.
   *
   * @param array $records
   *   Associative array of query results, keyed on the entity ID.
   * @param array $tables
   *   The table name for which to search the view for the entity type.
   *
   * @return \Drupal\Core\Entity\EntityType|null
   *   The entity type belonging to the given table or null if not found one.
   */
  protected function getEntityTypeFromView(array &$records, array $tables = []) {
    if (isset($records['display']) && is_array($records['display'])) {
      foreach ($records['display'] as &$display) {
        if (isset($display['display_options']) && is_array($display['display_options'])) {
          foreach ($display['display_options'] as &$display_options) {
            if (is_array($display_options)) {
              foreach ($display_options as &$display_option) {
                if (is_array($display_option) && isset($display_option['entity_type']) && !empty($display_option['table']) && in_array($display_option['table'], $tables, TRUE)) {
                  $entity_type = \Drupal::entityTypeManager()->getDefinition($display_option['entity_type']);
                  if (in_array($records['base_table'], $this->getEntityTables($entity_type), TRUE)) {
                    return $entity_type;
                  }
                }
              }
            }
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function doCreate(array $values) {
    // Update the values so that they are ready for the MongoDB backend.
    $values = $this->updateViewForMongodb($values);

    // Set default language to current language if not provided.
    $values += [$this->langcodeKey => $this->languageManager->getCurrentLanguage()->getId()];
    $entity = new $this->entityClass($values, $this->entityTypeId);

    return $entity;
  }

  /**
   * Helper method to get the MongoDB table information service.
   *
   * @return \Drupal\mongodb\Driver\Database\mongodb\TableInformation
   *   The MongoDB table information service.
   */
  protected function getTableInformation() {
    if (!isset($this->tableInformation)) {
      $this->tableInformation = $this->database->tableInformation();
    }
    return $this->tableInformation;
  }

}
