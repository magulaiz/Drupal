<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\Core\Database\Database;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\mongodb\Driver\Database\mongodb\EmbeddedTableData;

/**
 * The MongoDB implementation of \Drupal\Core\Entity\Sql\SqlContentEntityStorage.
 */
class ContentEntityStorage extends SqlContentEntityStorage {

  /**
   * The table that stores all revisions, if the entity supports revisions.
   *
   * @var string
   */
  protected $allRevisionsTable;

  /**
   * The table that stores the current revision, if the entity supports revisions.
   *
   * @var string
   */
  protected $currentRevisionTable;

  /**
   * The table that stores the latest revision, if the entity supports revisions.
   *
   * @var string
   */
  protected $latestRevisionTable;

  /**
   * The table that stores properties, if the entity has multilingual support.
   *
   * @var string
   */
  protected $translationsTable;

  /**
   * The MongoDB sequence service.
   *
   * @var \Drupal\mongodb\Driver\Database\mongodb\Sequences
   */
  protected $mongoSequences;

  /**
   * {@inheritdoc}
   */
  protected function initTableLayout() {
    // Reset table field values to ensure changes in the entity type definition
    // are correctly reflected in the table layout.
    $this->tableMapping = NULL;
    $this->revisionKey = NULL;
    $this->revisionTable = NULL;
    $this->dataTable = NULL;
    $this->revisionDataTable = NULL;

    // The MongoDB embedded tables.
    $this->allRevisionsTable = NULL;
    $this->currentRevisionTable = NULL;
    $this->latestRevisionTable = NULL;
    $this->translationsTable = NULL;

    $table_mapping = $this->getTableMapping();
    $this->baseTable = $table_mapping->getBaseTable();
    if ($this->entityType->isRevisionable()) {
      $this->revisionKey = $this->entityType->getKey('revision') ?: 'revision_id';
      $this->allRevisionsTable = $table_mapping->getAllRevisionsTable();
      $this->currentRevisionTable = $table_mapping->getCurrentRevisionTable();
      $this->latestRevisionTable = $table_mapping->getLatestRevisionTable();
    }
    if ($this->entityType->isTranslatable()) {
      $this->langcodeKey = $this->entityType->getKey('langcode');
      $this->defaultLangcodeKey = $this->entityType->getKey('default_langcode');
      if (!$this->entityType->isRevisionable()) {
        $this->translationsTable = $table_mapping->getTranslationsTable();
      }
    }
  }

  /**
   * Gets the MongoDB all revisions table name.
   *
   * @return string|false
   *   The table name or FALSE if it is not available.
   */
  public function getAllRevisionsTable() {
    return $this->allRevisionsTable;
  }

  /**
   * Gets the MongoDB current revision table name.
   *
   * @return string|false
   *   The table name or FALSE if it is not available.
   */
  public function getCurrentRevisionTable() {
    return $this->currentRevisionTable;
  }

  /**
   * Gets the MongoDB latest revision table name.
   *
   * @return string|false
   *   The table name or FALSE if it is not available.
   */
  public function getLatestRevisionTable() {
    return $this->latestRevisionTable;
  }

  /**
   * Gets the MongoDB translations table name.
   *
   * @return string|false
   *   The table name or FALSE if it is not available.
   */
  public function getTranslationsTable() {
    return $this->translationsTable;
  }

  /**
   * {@inheritdoc}
   */
  protected function getStorageSchema() {
    if (!isset($this->storageSchema)) {
      $class = $this->entityType->getHandlerClass('storage_schema') ?: 'Drupal\mongodb\EntityStorage\ContentEntityStorageSchema';
      $this->storageSchema = new $class($this->entityTypeManager, $this->entityType, $this, $this->database, $this->entityFieldManager);
    }
    return $this->storageSchema;
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomTableMapping(ContentEntityTypeInterface $entity_type, array $storage_definitions, $prefix = '') {
    $prefix = $prefix ?: ($this->temporary ? 'tmp_' : '');
    return DefaultTableMapping::create($entity_type, $storage_definitions, $prefix);
  }

  /**
   * {@inheritdoc}
   */
  protected function mapFromStorageRecords(array $records, $load_from_revision_id = FALSE) {
    if (!$records) {
      return [];
    }

    // $table_mapping = $this->getTableMapping();
    // $dedicated_table_names = $table_mapping->getDedicatedTableNames();

    // TODO remove: Get all the embedded table names without the base table.
    $embedded_table_names = $this->database->tableInformation()->getTableEmbeddedTables($this->baseTable);

    $values_embedded_tables = [];
    $values = [];
    foreach ($records as $id => $record) {
      $values[$id] = [];
      // Skip the item delta and item value levels (if possible) but let the
      // field assign the value as suiting. This avoids unnecessary array
      // hierarchies and saves memory here.
      foreach ($record as $name => $value) {
        // Handle columns named [field_name]__[column_name] (e.g for field types
        // that store several properties).
        if (in_array($name, $embedded_table_names, TRUE)) {
          // Add the embedded table data to the values array.
          $values_embedded_tables[$id][$name] = $value;
        }
        elseif ($field_name = strstr($name, '__', TRUE)) {
          $property_name = substr($name, strpos($name, '__') + 2);
          // TODO: Test if typecasting is necessary. Maybe special case if
          // $value is null.
          $values[$id][$field_name][LanguageInterface::LANGCODE_DEFAULT][$property_name] = (is_null($value) ? NULL : (string) $value);
        }
        else {
          // Handle columns named directly after the field (e.g if the field
          // type only stores one property).
          // TODO: Test if typecasting is necessary. Maybe special case if
          // $value is null.
          $values[$id][$name][LanguageInterface::LANGCODE_DEFAULT] = (is_null($value) ? NULL : (string) $value);
        }
      }

      if ($load_from_revision_id && ($record->{$this->revisionKey} != $load_from_revision_id)) {
        $values[$id]['isDefaultRevision'][LanguageInterface::LANGCODE_DEFAULT] = '0';
        $values[$id][$this->revisionKey][LanguageInterface::LANGCODE_DEFAULT] = (string) $load_from_revision_id;
      }
      else {
        $values[$id]['isDefaultRevision'][LanguageInterface::LANGCODE_DEFAULT] = '1';
      }
    }

    // Initialize translations array.
    $translations = array_fill_keys(array_keys($values), []);

    // Load values from shared and dedicated tables.
    $this->loadFromEmbeddedTables($values, $translations, $values_embedded_tables, $load_from_revision_id);

    $entities = [];
    foreach ($values as $id => $entity_values) {
      if ($this->bundleKey && isset($entity_values[$this->bundleKey][LanguageInterface::LANGCODE_DEFAULT])) {
        $bundle = $entity_values[$this->bundleKey][LanguageInterface::LANGCODE_DEFAULT];
      }
      else {
        $bundle = FALSE;
      }

      // Turn the record into an entity class.
      $entities[$id] = new $this->entityClass($entity_values, $this->entityTypeId, $bundle, array_keys($translations[$id]));
    }

    return $entities;
  }

  /**
   * Loads values for fields stored in the embedded tables.
   *
   * @param array &$values
   *   Associative array of entities values, keyed on the entity ID.
   * @param array &$translations
   *   List of translations, keyed on the entity ID.
   * @param array $values_embedded_tables
   *   The values of the embedded tables.
   * @param int|bool $load_from_revision_id
   *   Flag to indicate whether revisions should be loaded or not.
   */
  protected function loadFromEmbeddedTables(array &$values, array &$translations, array &$values_embedded_tables, $load_from_revision_id = FALSE) {
    if ($load_from_revision_id && $this->allRevisionsTable) {
      $embedded_table = $this->allRevisionsTable;
      $table_mapping = $this->getTableMapping();

      // Find revisioned fields that are not entity keys. Exclude the langcode
      // key as the base table holds only the default language.
      $base_fields = array_diff($table_mapping->getFieldNames($this->baseTable), [$this->langcodeKey]);

      $revisioned_fields = array_diff($table_mapping->getFieldNames($this->allRevisionsTable), [$this->idKey, $this->uuidKey]);

      // If there are no data fields then only revisioned fields are needed
      // else both data fields and revisioned fields are needed to map the
      // entity values.
      $all_fields = $revisioned_fields;
    }
    elseif ($this->currentRevisionTable) {
      $embedded_table = $this->currentRevisionTable;
      $table_mapping = $this->getTableMapping();

      // Find revisioned fields that are not entity keys. Exclude the langcode
      // key as the base table holds only the default language.
      $base_fields = array_diff($table_mapping->getFieldNames($this->baseTable), [$this->langcodeKey]);

      $revisioned_fields = array_diff($table_mapping->getFieldNames($this->currentRevisionTable), [$this->idKey, $this->uuidKey]);

      // If there are no data fields then only revisioned fields are needed
      // else both data fields and revisioned fields are needed to map the
      // entity values.
      $all_fields = $revisioned_fields;
    }
    elseif ($this->translationsTable) {
      $embedded_table = $this->translationsTable;
      $table_mapping = $this->getTableMapping();

      // Find revisioned fields that are not entity keys. Exclude the langcode
      // key as the base table holds only the default language.
      $base_fields = array_diff($table_mapping->getFieldNames($this->baseTable), [$this->langcodeKey]);

      $translations_fields = array_diff($table_mapping->getFieldNames($this->translationsTable), [$this->idKey, $this->uuidKey]);

      // If there are no data fields then only revisioned fields are needed
      // else both data fields and revisioned fields are needed to map the
      // entity values.
      $all_fields = $translations_fields;
    }
    else {
      $embedded_table = $this->baseTable;
      $base_fields = [];
      $all_fields = [];
    }

    // Get the field names for the "created" field types
    $storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions($this->entityTypeId);
    $created_fields = array_keys(array_filter($storage_definitions, function (FieldStorageDefinitionInterface $definition) {
      return $definition->getType() == 'created';
    }));

    $base_fields += [$this->revisionKey];
    $base_fields += [$this->langcodeKey];
    $base_fields = array_diff($base_fields, $created_fields);
    if (isset($revisioned_fields) && is_array($revisioned_fields)) {
      $base_fields = array_diff($base_fields, $revisioned_fields);
    }
    if (isset($translations_fields) && is_array($translations_fields)) {
      $base_fields = array_diff($base_fields, $translations_fields);
    }

    // Get the data table and the data revision table data.
    foreach ($values_embedded_tables as $id => $embedded_tables) {
      // Get the embedded table data for one entity.
      $embedded_table_data = [];
      foreach ($embedded_tables as $embedded_table_name => $embedded_table_rows) {
        if (!empty($embedded_table_name) && is_array($embedded_table_rows)) {
          if ($embedded_table_name == $this->translationsTable) {
            $embedded_table_data[$this->translationsTable] = $embedded_table_rows;
          }
          elseif (($embedded_table_name == $this->currentRevisionTable) && !$load_from_revision_id) {
            $embedded_table_data[$this->currentRevisionTable] = $embedded_table_rows;
          }
          elseif (($embedded_table_name == $this->allRevisionsTable) && $load_from_revision_id) {
            foreach ($embedded_table_rows as $embedded_table_revision) {
              if ($load_from_revision_id && isset($embedded_table_revision[$this->revisionKey]) && ($embedded_table_revision[$this->revisionKey] == $load_from_revision_id)) {
                $embedded_table_data[$this->allRevisionsTable][] = $embedded_table_revision;
              }
            }
          }
          elseif (!$this->translationsTable && !$this->currentRevisionTable && !$this->latestRevisionTable && !$this->allRevisionsTable) {
            $embedded_tables[$this->idKey] = $values[$id][$this->idKey][LanguageInterface::LANGCODE_DEFAULT];
            // TODO: Maybe there should be some else statement for the
            // next if statement.
            if (!empty($values[$id][$this->langcodeKey][LanguageInterface::LANGCODE_DEFAULT])) {
              $embedded_tables[$this->langcodeKey] = $values[$id][$this->langcodeKey][LanguageInterface::LANGCODE_DEFAULT];
            }
            $embedded_table_data[$this->baseTable] = [$embedded_tables];
          }
        }
      }

      // Use the collected embedded table data to retrieve the entity values.
      foreach ($embedded_table_data as $table_rows) {
        if (is_array($table_rows)) {
          foreach ($table_rows as $table_row) {
            $id = $table_row[$this->idKey];

            // Field values in default language are stored with
            // LanguageInterface::LANGCODE_DEFAULT as key.
            if (!empty($this->defaultLangcodeKey) && !empty($this->langcodeKey) && empty($table_row[$this->defaultLangcodeKey]) && !empty($table_row[$this->langcodeKey])) {
              $langcode = $table_row[$this->langcodeKey];
            }
            else {
              $langcode = LanguageInterface::LANGCODE_DEFAULT;
            }

            $langcode_is_default_langcode = FALSE;
            if (isset($values[$id][$this->langcodeKey][LanguageInterface::LANGCODE_DEFAULT]) && ($values[$id][$this->langcodeKey][LanguageInterface::LANGCODE_DEFAULT] == $langcode)) {
              $langcode_is_default_langcode = TRUE;
            }

            $translations[$id][$langcode] = TRUE;

            foreach ($all_fields as $field_name) {
              if (!in_array($field_name, $base_fields)) {
                $columns = $table_mapping->getColumnNames($field_name);

                // Do not key single-column fields by property name.
                if (count($columns) == 1) {
                  $values[$id][$field_name][$langcode] = (is_null($table_row[reset($columns)]) ? NULL : (string) $table_row[reset($columns)]);
                  if ($langcode_is_default_langcode) {
                    $values[$id][$field_name][LanguageInterface::LANGCODE_DEFAULT] = (is_null($table_row[reset($columns)]) ? NULL : (string) $table_row[reset($columns)]);
                  }
                }
                else {
                  foreach ($columns as $property_name => $column_name) {
                    $values[$id][$field_name][$langcode][$property_name] = (is_null($table_row[$column_name]) ? NULL : (string) $table_row[$column_name]);
                    if ($langcode_is_default_langcode) {
                      $values[$id][$field_name][LanguageInterface::LANGCODE_DEFAULT][$property_name] = (is_null($table_row[$column_name]) ? NULL : (string) $table_row[$column_name]);
                    }
                  }
                }
              }
            }
          }
        }
      }

      $this->loadFromEmbeddedDedicatedTables($values, $embedded_table, $embedded_table_data, $load_from_revision_id);
    }
  }

  /**
   * Loads values of fields stored in dedicated tables for a group of entities.
   *
   * @param array &$values
   *   An array of values keyed by entity ID.
   * @param string $embedded_table_name
   *   The embedded table name.
   * @param array $embedded_table_data
   *   The embedded table data.
   * @param bool $load_from_revision_id
   *   (optional) Flag to indicate whether revisions should be loaded or not,
   *   defaults to FALSE.
   */
  protected function loadFromEmbeddedDedicatedTables(array &$values, $embedded_table_name, array $embedded_table_data, $load_from_revision_id) {
    if (empty($values)) {
      return;
    }

    // Collect entities ids, bundles and languages.
    $bundles = [];
    $ids = [];
    $default_langcodes = [];
    foreach ($values as $key => $entity_values) {
      if ($this->bundleKey && !empty($entity_values[$this->bundleKey][LanguageInterface::LANGCODE_DEFAULT])) {
        $bundles[$entity_values[$this->bundleKey][LanguageInterface::LANGCODE_DEFAULT]] = TRUE;
      }
      else {
        $bundles[$this->entityTypeId] = TRUE;
      }
      $ids[] = !$load_from_revision_id ? $key : $entity_values[$this->revisionKey][LanguageInterface::LANGCODE_DEFAULT];
      if ($this->langcodeKey && isset($entity_values[$this->langcodeKey][LanguageInterface::LANGCODE_DEFAULT])) {
        $default_langcodes[$key] = $entity_values[$this->langcodeKey][LanguageInterface::LANGCODE_DEFAULT];
      }
    }

    // Collect impacted fields.
    $storage_definitions = [];
    $definitions = [];
    $table_mapping = $this->getTableMapping();
    foreach ($bundles as $bundle => $v) {
      $definitions[$bundle] = $this->entityFieldManager->getFieldDefinitions($this->entityTypeId, $bundle);
      foreach ($definitions[$bundle] as $field_name => $field_definition) {
        $storage_definition = $field_definition->getFieldStorageDefinition();
        if ($table_mapping->requiresDedicatedTableStorage($storage_definition)) {
          $storage_definitions[$field_name] = $storage_definition;
        }
      }
    }

    // Load field data.
    $langcodes = array_keys($this->languageManager->getLanguages(LanguageInterface::STATE_ALL));
    foreach ($storage_definitions as $field_name => $storage_definition) {
      $table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $embedded_table_name);

      if (isset($embedded_table_data[$embedded_table_name])) {
        $embedded_table_data = $embedded_table_data[$embedded_table_name];
      }

      $rows = [];
      $deltas = [];
      foreach ($embedded_table_data as $embedded_table_row) {
        foreach ($embedded_table_row as $embedded_table_key => $embedded_table_value) {
          if (($embedded_table_key == $table) && is_array($embedded_table_value)) {
            foreach ($embedded_table_value as $dedicated_table_row) {
              if (in_array($dedicated_table_row['langcode'], $langcodes, TRUE)) {
                if (!isset($dedicated_table_row['deleted']) || !$dedicated_table_row['deleted']) {
                  // Change the table row entity ID to an integer.
                  if (!$load_from_revision_id && in_array(intval($dedicated_table_row['entity_id']), $ids)) {
                    $rows[] = (object) $dedicated_table_row;
                    $deltas[] = $dedicated_table_row['delta'];
                  }
                  // Change the table row revision ID to an integer.
                  elseif ($load_from_revision_id && in_array(intval($dedicated_table_row['revision_id']), $ids)) {
                    $rows[] = (object) $dedicated_table_row;
                    $deltas[] = $dedicated_table_row['delta'];
                  }
                }
              }
            }
          }
        }
      }

      // Sort the dedicated rows according to their delta value.
      array_multisort($deltas, $rows);

      foreach ($rows as $row) {
        $bundle = $row->bundle;

        if (!in_array($row->langcode, $langcodes, TRUE)) {
          continue;
        }
        if (isset($row->deleted) && $row->deleted) {
          continue;
        }

        // Field values in default language are stored with
        // LanguageInterface::LANGCODE_DEFAULT as key.
        $langcode = LanguageInterface::LANGCODE_DEFAULT;
        if ($this->langcodeKey && isset($default_langcodes[$row->entity_id]) && $row->langcode != $default_langcodes[$row->entity_id]) {
          $langcode = $row->langcode;
        }

        if (!isset($values[$row->entity_id][$field_name][$langcode])) {
          $values[$row->entity_id][$field_name][$langcode] = [];
        }

        // Ensure that records for non-translatable fields having invalid
        // languages are skipped.
        if ($langcode == LanguageInterface::LANGCODE_DEFAULT || $definitions[$bundle][$field_name]->isTranslatable()) {
          if ($storage_definition->getCardinality() == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED || count($values[$row->entity_id][$field_name][$langcode]) < $storage_definition->getCardinality()) {
            $item = [];
            // For each column declared by the field, populate the item from the
            // prefixed database column.
            foreach ($storage_definition->getColumns() as $column => $attributes) {
              $column_name = $table_mapping->getFieldColumnName($storage_definition, $column);
              // Unserialize the value if specified in the column schema.
              $item[$column] = (!empty($attributes['serialize']) ? unserialize($row->$column_name) : $row->$column_name);
            }

            // Add the item to the field values for the entity.
            $values[$row->entity_id][$field_name][$langcode][] = $item;
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function doLoadRevisionFieldItems($revision_id) {
    $revision = NULL;

    // Build and execute the query.
    $query_result = $this->buildQuery([], $revision_id)->execute();
    $records = $query_result->fetchAllAssoc($this->idKey);

    if (!empty($records)) {
      // Convert the raw records to entity objects.
      $entities = $this->mapFromStorageRecords($records, $revision_id);
      $revision = reset($entities) ?: NULL;
    }

    return $revision;
  }

  /**
   * {@inheritdoc}
   */
  protected function doLoadMultipleRevisionsFieldItems($revision_ids) {
    $revisions = [];

    // Sanitize IDs. Before feeding ID array into buildQuery, check whether
    // it is empty as this would load all entity revisions.
    $revision_ids = $this->cleanIds($revision_ids, 'revision');
    if (!empty($revision_ids) && is_array($revision_ids)) {
      foreach ($revision_ids as $revision_id) {
        if ($revision = $this->doLoadRevisionFieldItems($revision_id)) {
          $revisions[$revision->getRevisionId()] = $revision;
        }
      }
    }

    return $revisions;
  }

  /**
   * {@inheritdoc}
   */
  protected function doDeleteRevisionFieldItems(ContentEntityInterface $revision) {
    $revision_id = (int) $revision->getRevisionId();

    $field_data = $this->database->tableInformation()->getTableField($this->baseTable, $this->idKey);
    if (isset($field_data['type']) && in_array($field_data['type'], ['int', 'serial'])) {
      $entity_id = (int) $revision->id();
    }
    else {
      $entity_id = (string) $revision->id();
    }

    $prefixed_table = $this->database->getMongodbPrefixedTable($this->baseTable);
    $update_operations = [];
    $update_operations['$pull'] = [$this->allRevisionsTable => [$this->revisionKey => $revision_id]];

    // Perform all update operations on the entity.
    $this->database->getConnection()->{$prefixed_table}->updateMany(
      [$this->idKey => $entity_id],
      $update_operations,
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function buildPropertyQuery(QueryInterface $entity_query, array $values) {
    if ($this->entityType->isTranslatable()) {
      // @todo We should not be using a condition to specify whether conditions
      //   apply to the default language. See
      //   https://www.drupal.org/node/1866330.
      // Default to the original entity language if not explicitly specified
      // otherwise.
      if (!array_key_exists($this->defaultLangcodeKey, $values)) {
        $values[$this->defaultLangcodeKey] = TRUE;
      }
      // If the 'default_langcode' flag is explicitly not set, we do not care
      // whether the queried values are in the original entity language or not.
      elseif ($values[$this->defaultLangcodeKey] === NULL) {
        unset($values[$this->defaultLangcodeKey]);
      }
    }

    parent::buildPropertyQuery($entity_query, $values);
  }

  /**
   * {@inheritdoc}
   */
  protected function buildQuery($ids, $revision_id = FALSE) {
    $query = $this->database->select($this->entityType->getBaseTable(), 'base');

    $query->addTag($this->entityTypeId . '_load_multiple');

    // Add fields from the {entity} table.
    $table_mapping = $this->getTableMapping();
    $entity_fields = $table_mapping->getAllColumns($this->baseTable);

    $query->fields('base', $entity_fields);

    $table_information = $this->database->tableInformation();
    $table_information->load(TRUE);
    $embedded_table_names = $table_information->getTableEmbeddedTables($this->entityType->getBaseTable());
    $query->fields('base', $embedded_table_names);

    if ($ids) {
      // MongoDB needs integer values to be real integers.
      $definition = $this->entityFieldManager->getFieldStorageDefinitions($this->entityTypeId)[$this->idKey];
      if ($definition->getType() == 'integer') {
        foreach ($ids as &$id) {
          $id = (int) $id;
        }
      }

      $query->condition("base.{$this->idKey}", $ids, 'IN');
    }

    if ($revision_id) {
      // MongoDB needs integer values to be real integers.
      $definition = $this->entityFieldManager->getFieldStorageDefinitions($this->entityTypeId)[$this->revisionKey];
      if ($definition->getType() == 'integer') {
        $revision_id = (int) $revision_id;
      }

      $all_revisions_table = $this->getAllRevisionsTable();
      $query->condition("base.$all_revisions_table.{$this->revisionKey}", $revision_id, '=');
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  protected function doDeleteFieldItems($entities) {
    $ids = array_keys($entities);

    $this->database->delete($this->entityType->getBaseTable())
      ->condition($this->idKey, $ids, 'IN')
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getLatestRevisionId($entity_id) {
    if (!$this->entityType->isRevisionable()) {
      return NULL;
    }

    if (!isset($this->latestRevisionIds[$entity_id][LanguageInterface::LANGCODE_DEFAULT])) {
      // Create for MongoDB a specific implementation for getting the latest
      // revision id. MongoDB stores all revision data in a single document/row.
      // As such there is no need for an aggregate query.
      $all_revisions = $this->database->select($this->getBaseTable(), 't')
        ->fields('t', [$this->allRevisionsTable])
        ->condition($this->entityType->getKey('id'), (int) $entity_id)
        ->execute()
        ->fetchField();

      $latest_revision_id = 0;
      $revision_key = $this->entityType->getKey('revision');
      if (!empty($all_revisions) && is_array($all_revisions)) {
        foreach ($all_revisions as $revision) {
          if (isset($revision[$revision_key]) && ($revision[$revision_key] > $latest_revision_id)) {
            $latest_revision_id = $revision[$revision_key];
          }
        }
      }

      $this->latestRevisionIds[$entity_id][LanguageInterface::LANGCODE_DEFAULT] = $latest_revision_id;
    }

    return $this->latestRevisionIds[$entity_id][LanguageInterface::LANGCODE_DEFAULT];
  }

  /**
   * {@inheritdoc}
   */
  protected function doSaveFieldItems(ContentEntityInterface $entity, array $names = []) {
    $update = !$entity->isNew();

    // MongoDB does not support auto-increments fields. So we need to add them
    // ourselves.
    if ($entity->id() === NULL) {
      $entity->set($this->idKey, $this->getMongoSequences()->nextEntityId($this->baseTable));
    }

    if ($this->entityType->isRevisionable() && $entity->isNewRevision()) {
      if ($entity->getRevisionId() === NULL) {
        $entity->set($this->entityType->getKey('revision'), $this->getMongoSequences()->nextRevisionId($this->baseTable));
      }
      else {
        // Make sure that the revision_id is not already in use.
        if ($this->loadRevision($entity->getRevisionId())) {
          $entity->set($this->entityType->getKey('revision'), $this->getMongoSequences()->nextRevisionId($this->baseTable));
        }

        if ($this->getMongoSequences()->currentRevisionId($this->baseTable) < $entity->getRevisionId()) {
          $this->getMongoSequences()->setRevisionId($this->baseTable, $entity->getRevisionId());
        }
      }
    }

    // Get the current revision ID, so that it can be set correctly in the base
    // table.
    if ($this->entityType->isRevisionable() && !$entity->isDefaultRevision() && ($current_revision = $this->load($entity->id()))) {
      $current_revision_id = $current_revision->getRevisionId();
    }

    if ($this->entityType->isTranslatable() && empty($entity->get($this->langcodeKey)->value)) {
      $entity->set($this->langcodeKey, $entity->language()->getId());
    }

    $record = $this->mapToStorageRecord($entity->getUntranslated(), $this->baseTable);
    $fields = (array) $record;

    if ($update) {
      $query = $this->database->update($this->baseTable)->condition($this->idKey, $record->{$this->idKey});
    }
    else {
      $query = $this->database->insert($this->baseTable, ['return' => Database::RETURN_INSERT_ID]);
    }

    $embedded_tables = [];
    if ($this->allRevisionsTable) {
      $embedded_tables[] = ['table' => $this->allRevisionsTable, 'update action' => 'append'];
    }
    // Not sure about the change on the next line. It fixes the EntityDuplicateTest.
    if ($this->currentRevisionTable && ($entity->isDefaultRevision() || ($entity->getRevisionId() == $entity->getLoadedRevisionId()))) {
      $embedded_tables[] = ['table' => $this->currentRevisionTable, 'update action' => 'replace'];
    }
    if ($this->latestRevisionTable && ($entity->isNewRevision() || ($entity->getRevisionId() >= $this->getLatestRevisionId($entity->id())))) {
      $embedded_tables[] = ['table' => $this->latestRevisionTable, 'update action' => 'replace'];
    }
    if ($this->translationsTable) {
      $embedded_tables[] = ['table' => $this->translationsTable, 'update action' => 'replace'];
    }

    // Get the dedicated table data for the all revisions, current revision,
    // latest revision and translations tables.
    $records_allDedicatedTables = $this->getEmbeddedDedicatedTablesRecords($entity, $names);
    if (empty($embedded_tables)) {
      // Get the dedicated table data for the base table.
      $records_dedicatedTables = isset($records_allDedicatedTables[$this->baseTable]) && is_array($records_allDedicatedTables[$this->baseTable]) ? $records_allDedicatedTables[$this->baseTable] : [];

      $record_baseTable = (array) $record;
      foreach ($records_dedicatedTables as $dedicated_table_name => $records_dedicatedTable) {
        $record_baseTable[$dedicated_table_name] = NULL;
        foreach ($records_dedicatedTable as $record_dedicatedTable) {
          // The base table idKey does not have to be off the same type as the
          // dedicated table entity_id (integer vs. string).
          if (($record_baseTable[$this->idKey] == $record_dedicatedTable['entity_id']) &&
              (empty($this->bundleKey) || ($record_baseTable[$this->bundleKey] === $record_dedicatedTable['bundle'])) &&
              (empty($this->langcodeKey) || ($record_baseTable[$this->langcodeKey] === $record_dedicatedTable['langcode']))) {
            if (!$record_baseTable[$dedicated_table_name] instanceof EmbeddedTableData) {
              $record_baseTable[$dedicated_table_name] = $query->embeddedTableData('replace')->fields($record_dedicatedTable);
            }
            else {
              $record_baseTable[$dedicated_table_name]->values($record_dedicatedTable);
            }
          }
        }
        $fields[$dedicated_table_name] = isset($record_baseTable[$dedicated_table_name]) ? $record_baseTable[$dedicated_table_name] : NULL;
      }

      // Dedicated fields with no values set must be set to NULL.
      $dedicated_table_names = $this->getEmbeddedDedicatedTableNames($entity, $names);
      if (is_array($dedicated_table_names[$this->baseTable])) {
        foreach ($dedicated_table_names[$this->baseTable] as $dedicated_table_name) {
          if (!isset($fields[$dedicated_table_name])) {
            $fields[$dedicated_table_name] = NULL;
          }
        }
      }
    }
    else {
      foreach ($embedded_tables as $embedded_table) {
        $embedded_table_name = $embedded_table['table'];

        // Get the dedicated table data for the embedded table.
        $records_dedicatedTables = isset($records_allDedicatedTables[$embedded_table_name]) && is_array($records_allDedicatedTables[$embedded_table_name]) ? $records_allDedicatedTables[$embedded_table_name] : [];

        // Get the embedded table data.
        $records_embeddedTable = $this->getEmbeddedTableRecords($entity, $embedded_table_name);

        $data_embeddedTable = NULL;
        foreach ($records_embeddedTable as $record_embeddedTable) {
          // Add the dedicated table data to the embedded table row data.
          foreach ($records_dedicatedTables as $dedicated_table_name => $records_dedicatedTable) {
            $record_embeddedTable[$dedicated_table_name] = NULL;
            foreach ($records_dedicatedTable as $record_dedicatedTable) {
              // The base table idKey does not have to be off the same type as
              // the dedicated table entity_id (integer vs. string).
              if ((empty($this->revisionKey) || ($record_embeddedTable[$this->revisionKey] == $record_dedicatedTable['revision_id'])) &&
                  (empty($this->bundleKey) || ($record_embeddedTable[$this->bundleKey] === $record_dedicatedTable['bundle'])) &&
                  (empty($this->langcodeKey) || ($record_embeddedTable[$this->langcodeKey] === $record_dedicatedTable['langcode']))) {
                if (!$record_embeddedTable[$dedicated_table_name] instanceof EmbeddedTableData) {
                  $record_embeddedTable[$dedicated_table_name] = $query->embeddedTableData()->fields($record_dedicatedTable);
                }
                else {
                  $record_embeddedTable[$dedicated_table_name]->values($record_dedicatedTable);
                }
              }
            }
          }

          // Create the embedded table rows.
          if (!$data_embeddedTable instanceof EmbeddedTableData) {
            if ($update && $embedded_table['update action'] === 'append') {
              $action = 'append';
            }
            elseif ($update && $embedded_table['update action'] === 'replace') {
              $action = 'replace';
            }
            else {
              $action = '';
            }
            $data_embeddedTable = $query->embeddedTableData($action)->fields($record_embeddedTable);
          }
          else {
            $data_embeddedTable->values($record_embeddedTable);
          }
        }
        $fields[$embedded_table_name] = isset($data_embeddedTable) ? $data_embeddedTable : NULL;
      }
    }

    if ($update) {
      // Make sure that the revision_id in the base table has the value of the
      // current revision.
      if (!empty($this->revisionKey) && !empty($fields[$this->revisionKey]) && !empty($current_revision_id)) {
        $fields[$this->revisionKey] = (int) $current_revision_id;
      }

      $query->fields($fields);
      $query->execute();

      if ($this->entityType->isRevisionable() && !$entity->isNewRevision()) {
        // When updating an entity with revisions and without creating a new
        // revision creates a problem with MongoDB. The embedded table holding
        // all the revision data can on update do only one change to the
        // embedded table data. The new revision data is added to the embedded
        // table data. In the embedded table holding the all revision data there
        // are now two sets of revision data for the same revision. When
        // querying the entity for revision data the query will fail, because
        // there are two sets of revision data. The older revision data needs to
        // be removed.
        $this->cleanupEntityAllRevisionData($entity->id());
      }
    }
    else {
      $query->fields($fields);
      $insert_id = $query->execute();

      // Even if this is a new entity the ID key might have been set, in which
      // case we should not override the provided ID. An ID key that is not set
      // to any value is interpreted as NULL (or DEFAULT) and thus overridden.
      if (!isset($record->{$this->idKey})) {
        $record->{$this->idKey} = $insert_id;
      }
      $entity->{$this->idKey} = (string) $record->{$this->idKey};
    }
  }

  /**
   * Removes the unneeded revisions from the all_revisions table.
   *
   * @param string|int $entity_id
   *   The table name to save to. Defaults to the data table.
   */
  protected function cleanupEntityAllRevisionData($entity_id) {
    try {
      // Only do this if the entity is revisionable.
      if ($this->entityType->isRevisionable()) {
        // Make sure that the entity_id is of the correct type (integer or string).
        $base_table_entity_id_data = $this->database->tableInformation()->getTableField($this->baseTable, $this->idKey);
        if (isset($base_table_entity_id_data['type']) && in_array($base_table_entity_id_data['type'], ['int', 'serial'])) {
          $entity_id = (int) $entity_id;
        }
        else {
          $entity_id = (string) $entity_id;
        }

        $prefixed_table = $this->database->getMongodbPrefixedTable($this->baseTable);
        $entity_data = $this->database->getConnection()->{$prefixed_table}->findOne(
          [$this->idKey => ['$eq' => $entity_id]],
          ['projection' => [$this->allRevisionsTable => 1]],
        );

        $revisions_langcodes = [];
        $new_all_revisions_data = [];
        if (isset($entity_data->{$this->allRevisionsTable})) {
          $all_revisions_data = (array) $entity_data->{$this->allRevisionsTable};
          $all_revisions_data = array_reverse($all_revisions_data);
          foreach ($all_revisions_data as $revision) {
            $exists = FALSE;
            foreach ($revisions_langcodes as $revision_langcode) {
              if (($revision_langcode['revision_id'] == $revision->{$this->revisionKey}) && ($revision_langcode['langcode'] == $revision->{$this->langcodeKey})) {
                $exists = TRUE;
              }
            }
            if (!$exists) {
              $revisions_langcodes[] = [
                'revision_id' => $revision->{$this->revisionKey},
                'langcode' => $revision->{$this->langcodeKey},
              ];
              $new_all_revisions_data[] = clone $revision;
            }
          }
        }
        $new_all_revisions_data = array_reverse($new_all_revisions_data);

        $this->database->getConnection()->{$prefixed_table}->updateOne(
          [$this->idKey => ['$eq' => $entity_id]],
          ['$set' => [$this->allRevisionsTable => $new_all_revisions_data]],
        );
      }
    }
    catch (\Exception $e) {
      // Throw exception that we could not load the entity.
    }
  }

  /**
   * Get the fields to be saved from the embedded tables.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity object.
   * @param string $table_name
   *   The table name to save to. Defaults to the data table.
   *
   * @return array
   *   The records to store for the shared table
   */
  protected function getEmbeddedTableRecords(ContentEntityInterface $entity, $table_name) {
    $records = [];
    foreach ($entity->getTranslationLanguages() as $langcode => $language) {
      $translation = $entity->getTranslation($langcode);
      $records[] = (array) $this->mapToStorageRecord($translation, $table_name);
    }

    return $records;
  }

  /**
   * Get the fields to be saved from the embedded dedicated tables.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity object.
   * @param string $names
   *   The table names to save to. Defaults to the data table.
   *
   * @return array
   *   The records to store for the shared table
   */
  protected function getEmbeddedDedicatedTableNames(ContentEntityInterface $entity, $names = []) {
    $bundle = $entity->bundle();
    $entity_type = $entity->getEntityTypeId();
    $table_mapping = $this->getTableMapping();

    $original = !empty($entity->original) ? $entity->original : NULL;

    // Determine which fields should be actually stored.
    $definitions = $this->entityFieldManager->getFieldDefinitions($entity_type, $bundle);
    if ($names) {
      $definitions = array_intersect_key($definitions, array_flip($names));
    }

    $dedicated_table_names = [];
    if ($this->allRevisionsTable) {
      $dedicated_table_names[$this->allRevisionsTable] = [];
    }
    if ($this->currentRevisionTable) {
      $dedicated_table_names[$this->currentRevisionTable] = [];
    }
    if ($this->latestRevisionTable) {
      $dedicated_table_names[$this->latestRevisionTable] = [];
    }
    if ($this->translationsTable) {
      $dedicated_table_names[$this->translationsTable] = [];
    }
    if (!$this->allRevisionsTable && !$this->currentRevisionTable && !$this->latestRevisionTable && !$this->translationsTable) {
      $dedicated_table_names[$this->baseTable] = [];
    }

    foreach ($definitions as $field_definition) {
      $storage_definition = $field_definition->getFieldStorageDefinition();
      if (!$table_mapping->requiresDedicatedTableStorage($storage_definition)) {
        continue;
      }

      // TODO: Test if the code that is below can be deleted.
      // When updating an existing revision, keep the existing records if the
      // field values did not change.
      if (!$entity->isNewRevision() && $original && !$this->hasFieldValueChanged($field_definition, $entity, $original)) {
        continue;
      }

      if ($this->allRevisionsTable) {
        $dedicated_table_names[$this->allRevisionsTable][] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->allRevisionsTable);
      }

      if ($this->currentRevisionTable) {
        $dedicated_table_names[$this->currentRevisionTable][] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->currentRevisionTable);
      }

      if ($this->latestRevisionTable) {
        $dedicated_table_names[$this->latestRevisionTable][] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->latestRevisionTable);
      }

      if ($this->translationsTable) {
        $dedicated_table_names[$this->translationsTable][] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->translationsTable);
      }

      if (!$this->allRevisionsTable && !$this->currentRevisionTable && !$this->latestRevisionTable && !$this->translationsTable) {
        $dedicated_table_names[$this->baseTable][] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->baseTable);
      }
    }

    return $dedicated_table_names;
  }

  /**
   * Saves values of fields that use embedded dedicated tables.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param string[] $names
   *   (optional) The names of the fields to be stored. Defaults to all the
   *   available fields.
   */
  protected function getEmbeddedDedicatedTablesRecords(ContentEntityInterface $entity, $names = []) {
    $vid = $entity->getRevisionId();
    $id = $entity->id();
    $bundle = $entity->bundle();
    $entity_type = $entity->getEntityTypeId();
    $translation_langcodes = array_keys($entity->getTranslationLanguages());
    $table_mapping = $this->getTableMapping();

    if (!isset($vid)) {
      $vid = $id;
    }

    // Determine which fields should be actually stored.
    $definitions = $this->entityFieldManager->getFieldDefinitions($entity_type, $bundle);
    if ($names) {
      $definitions = array_intersect_key($definitions, array_flip($names));
    }

    $records = [];

    if ($this->allRevisionsTable) {
      $records[$this->allRevisionsTable] = [];
    }
    if ($this->currentRevisionTable) {
      $records[$this->currentRevisionTable] = [];
    }
    if ($this->latestRevisionTable) {
      $records[$this->latestRevisionTable] = [];
    }
    if ($this->translationsTable) {
      $records[$this->translationsTable] = [];
    }
    if (!$this->allRevisionsTable && !$this->currentRevisionTable && !$this->latestRevisionTable && !$this->translationsTable) {
      $records[$this->baseTable] = [];
    }

    foreach ($definitions as $field_name => $field_definition) {
      $storage_definition = $field_definition->getFieldStorageDefinition();
      if (!$table_mapping->requiresDedicatedTableStorage($storage_definition)) {
        continue;
      }

      $dedicated_all_revisions_table_name = NULL;
      if ($this->allRevisionsTable) {
        $dedicated_all_revisions_table_name = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->allRevisionsTable);
      }

      $dedicated_current_revision_table_name = NULL;
      if ($this->currentRevisionTable) {
        $dedicated_current_revision_table_name = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->currentRevisionTable);
      }

      $dedicated_latest_revision_table_name = NULL;
      if ($this->latestRevisionTable) {
        $dedicated_latest_revision_table_name = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->latestRevisionTable);
      }

      $dedicated_translations_table_name = NULL;
      if ($this->translationsTable) {
        $dedicated_translations_table_name = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->translationsTable);
      }

      $dedicated_base_table_name = NULL;
      if (!$this->allRevisionsTable && !$this->currentRevisionTable && !$this->latestRevisionTable && !$this->translationsTable) {
        $dedicated_base_table_name = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->baseTable);
      }

      // Prepare the multi-insert query.
      $columns = ['entity_id', 'revision_id', 'bundle', 'delta', 'langcode'];
      foreach ($storage_definition->getColumns() as $column => $attributes) {
        $columns[] = $table_mapping->getFieldColumnName($storage_definition, $column);
      }

      // Save all non-translatable fields for all languages. They belong to
      // every language. This is also needs for entity filter purposes.
      foreach ($translation_langcodes as $langcode) {
        $delta_count = 0;
        $items = $entity->getTranslation($langcode)->get($field_name);
        $items->filterEmptyItems();
        foreach ($items as $delta => $item) {
          // We now know we have something to insert.
          $record = [
            'entity_id' => $id,
            'revision_id' => $vid,
            'bundle' => $bundle,
            'delta' => $delta,
            'langcode' => $langcode,
          ];
          foreach ($storage_definition->getColumns() as $column => $attributes) {
            $column_name = $table_mapping->getFieldColumnName($storage_definition, $column);
            $value = $item->$column;
            if (!empty($attributes['serialize'])) {
              $value = serialize($value);
            }
            $record[$column_name] = ContentEntityStorageSchema::castValue($attributes, $value);
          }
          if (isset($records[$this->allRevisionsTable]) && is_array($records[$this->allRevisionsTable])) {
            $records[$this->allRevisionsTable][$dedicated_all_revisions_table_name][] = $record;
          }
          if (isset($records[$this->currentRevisionTable]) && is_array($records[$this->currentRevisionTable])) {
            $records[$this->currentRevisionTable][$dedicated_current_revision_table_name][] = $record;
          }
          if (isset($records[$this->latestRevisionTable]) && is_array($records[$this->latestRevisionTable])) {
            $records[$this->latestRevisionTable][$dedicated_latest_revision_table_name][] = $record;
          }
          if (isset($records[$this->translationsTable]) && is_array($records[$this->translationsTable])) {
            $records[$this->translationsTable][$dedicated_translations_table_name][] = $record;
          }
          if (isset($records[$this->baseTable]) && is_array($records[$this->baseTable])) {
            $records[$this->baseTable][$dedicated_base_table_name][] = $record;
          }

          if ($storage_definition->getCardinality() != FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED && ++$delta_count == $storage_definition->getCardinality()) {
            break;
          }
        }
      }
    }

    return $records;
  }

  /**
   * {@inheritdoc}
   */
  protected function getQueryServiceName() {
    return 'mongodb.entity.query.sql';
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldStorageDefinitionDelete(FieldStorageDefinitionInterface $storage_definition) {
    $table_mapping = $this->getTableMapping(
    // $this->entityManager->getLastInstalledFieldStorageDefinitions($this->entityType->id())
    );

    if ($table_mapping->requiresDedicatedTableStorage($storage_definition)) {
      $revisionable = $this->entityType->isRevisionable();
      $translatable = $this->entityType->isTranslatable();

      $dedicated_tables = [];
      if ($revisionable) {
        $dedicated_tables[$this->getAllRevisionsTable()] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getAllRevisionsTable());
        $dedicated_tables[$this->getCurrentRevisionTable()] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getCurrentRevisionTable());
        $dedicated_tables[$this->getLatestRevisionTable()] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getLatestRevisionTable());
      }
      if (!$revisionable && $translatable) {
        $dedicated_tables[$this->getTranslationsTable()] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getTranslationsTable());
      }
      if (!$revisionable && !$translatable) {
        $dedicated_tables[$this->getBaseTable()] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getBaseTable());
      }

      foreach ($dedicated_tables as $embedded_to_table => $dedicated_table) {
        $prefixed_table = $this->database->getMongodbPrefixedTable($this->getBaseTable());
        if ($embedded_to_table == $this->getBaseTable()) {
          $this->database->getConnection()->{$prefixed_table}->updateMany(
            ["$dedicated_table" => ['$exists' => TRUE]],
            ['$set' => ["$dedicated_table.$[].deleted" => TRUE]],
          );
        }
        else {
          $this->database->getConnection()->{$prefixed_table}->updateMany(
            ["$embedded_to_table.$dedicated_table" => ['$exists' => TRUE]],
            ['$set' => ["$embedded_to_table.$[].$dedicated_table.$[].deleted" => TRUE]],
          );
        }
      }
    }

    // Update the field schema.
    $this->wrapSchemaException(function () use ($storage_definition) {
      $this->getStorageSchema()->onFieldStorageDefinitionDelete($storage_definition);
    });
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldDefinitionDelete(FieldDefinitionInterface $field_definition) {
    $table_mapping = $this->getTableMapping();
    $storage_definition = $field_definition->getFieldStorageDefinition();
    // Mark field data as deleted.
    if ($table_mapping->requiresDedicatedTableStorage($storage_definition)) {
      $prefixed_table = $this->database->getMongodbPrefixedTable($this->getBaseTable());

      if ($this->entityType->isRevisionable()) {
        $all_revisions_table = $this->getAllRevisionsTable();
        $dedicated_all_revisions_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $all_revisions_table);
        $current_revision_table = $this->getCurrentRevisionTable();
        $dedicated_current_revision_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $current_revision_table);
        $latest_revision_table = $this->getLatestRevisionTable();
        $dedicated_latest_revision_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $latest_revision_table);

        $this->database->getConnection()->{$prefixed_table}->updateMany(
          [
            "$current_revision_table.$dedicated_current_revision_table" => ['$exists' => TRUE],
          ],
          [
            '$set' => [
              "$current_revision_table.$[].$dedicated_current_revision_table.$[field].deleted" => TRUE,
            ],
          ],
          ['arrayFilters' => [["field.bundle" => $field_definition->getTargetBundle()]]],
        );

        $this->database->getConnection()->{$prefixed_table}->updateMany(
          [
            "$latest_revision_table.$dedicated_latest_revision_table" => ['$exists' => TRUE],
          ],
          [
            '$set' => [
              "$latest_revision_table.$[].$dedicated_latest_revision_table.$[field].deleted" => TRUE,
            ],
          ],
          ['arrayFilters' => [["field.bundle" => $field_definition->getTargetBundle()]]]
        );

        $this->database->getConnection()->{$prefixed_table}->updateMany(
          [],
          [
            '$set' => [
              "$all_revisions_table.$[dedicated].$dedicated_all_revisions_table.$[].deleted" => TRUE,
            ],
          ],
          [
            'arrayFilters' => [
              ["dedicated.$dedicated_all_revisions_table" => ['$exists' => TRUE]],
            ],
          ],
        );
      }
      elseif ($this->entityType->isTranslatable()) {
        $translations_table = $this->getTranslationsTable();
        $dedicated_translations_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $translations_table);

        $this->database->getConnection()->{$prefixed_table}->updateMany(
          [
            "$translations_table.$dedicated_translations_table" => ['$exists' => TRUE],
          ],
          [
            '$set' => [
              "$translations_table.$[].$dedicated_translations_table.$[field].deleted" => TRUE,
            ],
          ],
          ['arrayFilters' => [["field.bundle" => $field_definition->getTargetBundle()]]],
        );
      }
      else {
        $base_table = $this->getBaseTable();
        $dedicated_base_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $base_table);
        $this->database->getConnection()->{$prefixed_table}->updateMany(
          [
            $dedicated_base_table => ['$exists' => TRUE],
          ],
          [
            '$set' => [
              "$dedicated_base_table.$[field].deleted" => TRUE,
            ],
          ],
          ['arrayFilters' => [["field.bundle" => $field_definition->getTargetBundle()]]],
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function readFieldItemsToPurge(FieldDefinitionInterface $field_definition, $batch_size) {
    $storage_definition = $field_definition->getFieldStorageDefinition();
    $is_deleted = $storage_definition->isDeleted();
    $table_mapping = $this->getTableMapping();

    $column_map = [];
    foreach ($storage_definition->getColumns() as $column_name => $data) {
      $column_map[$table_mapping->getFieldColumnName($storage_definition, $column_name)] = $column_name;
    }

    $dedicated_tables = [];
    if ($this->entityType->isRevisionable()) {
      $dedicated_tables[$this->getAllRevisionsTable()] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getAllRevisionsTable(), $is_deleted);
      $dedicated_tables[$this->getCurrentRevisionTable()] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getCurrentRevisionTable(), $is_deleted);
      $dedicated_tables[$this->getLatestRevisionTable()] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getLatestRevisionTable(), $is_deleted);
    }
    elseif (!$this->entityType->isRevisionable() && $this->entityType->isTranslatable()) {
      $dedicated_tables[$this->getTranslationsTable()] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getTranslationsTable(), $is_deleted);
    }
    elseif (!$this->entityType->isRevisionable() && !$this->entityType->isTranslatable()) {
      $dedicated_tables[$this->getBaseTable()] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getBaseTable(), $is_deleted);
    }

    reset($dedicated_tables);
    $embedded_to_table = key($dedicated_tables);
    $dedicated_table = current($dedicated_tables);

    // Get the entities which we want to purge first.
    $entity_query = $this->database->select($this->getBaseTable(), 't');
    if ($embedded_to_table == $this->getBaseTable()) {
      $entity_query->isNotNull($dedicated_table);
      $entity_query->condition("$dedicated_table.bundle", $field_definition->getTargetBundle());
      $entity_query->fields('t', ["$dedicated_table"]);
    }
    else {
      $entity_query->isNotNull("$embedded_to_table.$dedicated_table");
      $entity_query->condition("$embedded_to_table.$dedicated_table.bundle", $field_definition->getTargetBundle());
    }
    $entity_query->range(0, $batch_size);

    $entities = [];
    $items_by_entity = [];
    foreach ($entity_query->execute() as $row) {
      if ($embedded_to_table == $this->getBaseTable()) {
        $dedicated_table_rows = $row->$dedicated_table;
      }
      else {
        $dedicated_table_rows = [];
        $embedded_to_table_rows = $row->$embedded_to_table;
        foreach ($embedded_to_table_rows as $embedded_to_table_row) {
          $dedicated_table_rows += $embedded_to_table_row[$dedicated_table];
        }
      }
      if (is_array($dedicated_table_rows)) {
        foreach ($dedicated_table_rows as $dedicated_table_row) {
          if (!isset($entities[$dedicated_table_row['revision_id']])) {
            // Create entity with the right revision id and entity id combination.
            $dedicated_table_row['entity_type'] = $this->entityTypeId;
            // @todo: Replace this by an entity object created via an entity
            // factory, see https://www.drupal.org/node/1867228.
            $entities[$dedicated_table_row['revision_id']] = _field_create_entity_from_ids((object) $dedicated_table_row);
          }
          $item = [];
          foreach ($column_map as $db_column => $field_column) {
            $item[$field_column] = $dedicated_table_row[$db_column];
          }
          $items_by_entity[$dedicated_table_row['revision_id']][] = $item;
        }
      }
    }

    // Create field item objects and return.
    foreach ($items_by_entity as $revision_id => $values) {
      $entity_adapter = $entities[$revision_id]->getTypedData();
      $items_by_entity[$revision_id] = \Drupal::typedDataManager()->create($field_definition, $values, $field_definition->getName(), $entity_adapter);
    }

    return $items_by_entity;
  }

  /**
   * {@inheritdoc}
   */
  protected function purgeFieldItems(ContentEntityInterface $entity, FieldDefinitionInterface $field_definition) {
    $storage_definition = $field_definition->getFieldStorageDefinition();
    $is_deleted = $storage_definition->isDeleted();
    $table_mapping = $this->getTableMapping();
    $id = $this->entityType->isRevisionable() ? $entity->getRevisionId() : $entity->id();
    $id_key = $this->entityType->isRevisionable() ? $this->revisionKey : $this->idKey;

    $dedicated_tables = [];
    if ($this->entityType->isRevisionable()) {
      $dedicated_tables[$this->getAllRevisionsTable()] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getAllRevisionsTable(), $is_deleted);
      $dedicated_tables[$this->getCurrentRevisionTable()] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getCurrentRevisionTable(), $is_deleted);
      $dedicated_tables[$this->getLatestRevisionTable()] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getLatestRevisionTable(), $is_deleted);
    }
    elseif (!$this->entityType->isRevisionable() && $this->entityType->isTranslatable()) {
      $dedicated_tables[$this->getTranslationsTable()] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getTranslationsTable(), $is_deleted);
    }
    elseif (!$this->entityType->isRevisionable() && !$this->entityType->isTranslatable()) {
      $dedicated_tables[$this->getBaseTable()] = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getBaseTable(), $is_deleted);
    }

    $field_info = $this->database->tableInformation()->getTableField($this->getBaseTable(), $id_key);
    if (isset($field_info['type']) && in_array($field_info['type'], ['int', 'serial'], TRUE)) {
      $id = (int) $id;
    }

    $prefixed_table = $this->database->getMongodbPrefixedTable($this->getBaseTable());
    foreach ($dedicated_tables as $embedded_to_table => $dedicated_table) {
      if ($embedded_to_table == $this->getBaseTable()) {
        $this->database->getConnection()->{$prefixed_table}->updateMany(
          [
            $dedicated_table => ['$exists' => TRUE],
            $id_key => $id,
          ],
          ['$unset' => [$dedicated_table => ""]],
        );
      }
      else {
        $this->database->getConnection()->{$prefixed_table}->updateMany(
          [
            "$embedded_to_table.$dedicated_table" => ['$exists' => TRUE],
            $id_key => $id,
          ],
          ['$unset' => ["$embedded_to_table.$dedicated_table" => ""]],
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function countFieldData($storage_definition, $as_bool = FALSE) {
    // $storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions($this->entityTypeId);
    $storage_definitions = $this->fieldStorageDefinitions;
    $storage_definitions[$storage_definition->getName()] = $storage_definition;
    $table_mapping = $this->getTableMapping($storage_definitions);

    if ($table_mapping->requiresDedicatedTableStorage($storage_definition)) {
      $query = $this->database->select($this->getBaseTable(), 't');
      $or = $query->orConditionGroup();

      $is_deleted = $storage_definition->isDeleted();
      if ($this->entityType->isRevisionable()) {
        $dedicated_all_revisions_table_name = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getAllRevisionsTable(), $is_deleted);
        $or->isNotNull($this->getAllRevisionsTable() . '.' . $dedicated_all_revisions_table_name);
      }
      elseif ($this->entityType->isTranslatable()) {
        $dedicated_translations_table_name = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getTranslationsTable(), $is_deleted);
        $or->isNotNull($this->getTranslationsTable() . '.' . $dedicated_translations_table_name);
      }
      else {
        $dedicated_base_table_name = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->getBaseTable(), $is_deleted);
        $or->isNotNull($dedicated_base_table_name);
      }

      $query
        ->condition($or)
        ->fields('t', [$this->idKey]);
    }
    elseif ($table_mapping->allowsSharedTableStorage($storage_definition)) {
      $query = $this->database->select($this->getBaseTable(), 't');
      $or = $query->orConditionGroup();

      foreach (array_keys($storage_definition->getColumns()) as $property_name) {
        if ($this->entityType->isRevisionable()) {
          $or->isNotNull($this->getAllRevisionsTable() . '.' . $table_mapping->getFieldColumnName($storage_definition, $property_name));
          $or->isNotNull($this->getCurrentRevisionTable() . '.' . $table_mapping->getFieldColumnName($storage_definition, $property_name));
          $or->isNotNull($this->getLatestRevisionTable() . '.' . $table_mapping->getFieldColumnName($storage_definition, $property_name));
        }
        elseif ($this->entityType->isTranslatable()) {
          $or->isNotNull($this->getTranslationsTable() . '.' . $table_mapping->getFieldColumnName($storage_definition, $property_name));
        }
        else {
          $or->isNotNull($table_mapping->getFieldColumnName($storage_definition, $property_name));
        }
      }

      $query
        ->condition($or)
        ->fields('t', [$this->idKey]);
    }

    // @todo Find a way to count field data also for fields having custom
    //   storage. See https://www.drupal.org/node/2337753.
    $count = 0;
    if (isset($query)) {
      // If we are performing the query just to check if the field has data
      // limit the number of rows.
      if ($as_bool) {
        $query->range(0, 1);
        $rows = $query->execute()->fetchAll();
        $count = count($rows);
      }
      else {
        // Otherwise count the number of rows.
        $query = $query->countQuery();
        $count = $query->execute()->fetchField();
      }
    }

    return $as_bool ? (bool) $count : (int) $count;
  }

  /**
   * Helper method to get the MongoDB table information service.
   *
   * @return \Drupal\mongodb\Driver\Database\mongodb\TableInformation
   *   The MongoDB table information service.
   */
  protected function getMongoSequences() {
    if (!isset($this->mongoSequences)) {
      $this->mongoSequences = \Drupal::service('mongodb.sequences');
    }
    return $this->mongoSequences;
  }

}
