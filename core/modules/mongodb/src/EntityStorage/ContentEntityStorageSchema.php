<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Exception\FieldStorageDefinitionUpdateForbiddenException;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Entity\Sql\TableMappingInterface;
use Drupal\Core\Field\FieldException;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * The MongoDB implementation of \Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema.
 */
class ContentEntityStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  public function onFieldableEntityTypeCreate(EntityTypeInterface $entity_type, array $field_storage_definitions) {
    // When installing a fieldable entity type, we have to use the provided
    // entity type and field storage definitions.
    $this->entityType = $entity_type;
    $this->fieldStorageDefinitions = $field_storage_definitions;

    $this->checkEntityType($entity_type);
    $schema_handler = $this->database->schema();
    $base_table = $entity_type->getBaseTable();

    // Create entity tables.
    $schema = $this->getEntitySchema($entity_type, TRUE);

    // Create the base table first.
    if (!empty($schema[$base_table]) && !$schema_handler->tableExists($base_table)) {
      $schema_handler->createTable($base_table, $schema[$base_table]);
    }

    // Create now all embedded tables.
    foreach ($schema as $table_name => $table_schema) {
      if (($base_table != $table_name) && !$schema_handler->tableExists($table_name)) {
        $schema_handler->createEmbeddedTable($base_table, $table_name, $table_schema);
      }
    }


    // Create dedicated field tables.
//    $table_mapping = $this->getTableMapping($entity_type, $this->fieldStorageDefinitions);
    $table_mapping = $this->getTableMapping($entity_type);
    foreach ($this->fieldStorageDefinitions as $field_storage_definition) {
      if ($table_mapping->requiresDedicatedTableStorage($field_storage_definition)) {
        $this->createDedicatedTableSchema($field_storage_definition);
      }
      elseif ($table_mapping->allowsSharedTableStorage($field_storage_definition)) {
        // The shared tables are already fully created, but we need to save the
        // per-field schema definitions for later use.
        $this->createSharedTableSchema($field_storage_definition, TRUE);
      }
    }

    // Save data about entity indexes and keys.
    $this->saveEntitySchemaData($entity_type, $schema);
  }

  /**
   * {@inheritdoc}
   *
  public function onEntityTypeCreate(EntityTypeInterface $entity_type) {
    $this->checkEntityType($entity_type);
    $schema_handler = $this->database->schema();
    $base_table = $entity_type->getBaseTable();

    // Create entity tables.
    $schema = $this->getEntitySchema($entity_type, TRUE);

    // Create the base table first.
    if (!empty($schema[$base_table]) && !$schema_handler->tableExists($base_table)) {
      $schema_handler->createTable($base_table, $schema[$base_table]);
    }

    // Create now all embedded tables.
    foreach ($schema as $table_name => $table_schema) {
      if (($base_table != $table_name) && !$schema_handler->tableExists($table_name)) {
        $schema_handler->createEmbeddedTable($base_table, $table_name, $table_schema);
      }
    }

    // Create dedicated field tables.
    $table_mapping = $this->getTableMapping($entity_type, $this->fieldStorageDefinitions);
    foreach ($this->fieldStorageDefinitions as $field_storage_definition) {
      if ($table_mapping->requiresDedicatedTableStorage($field_storage_definition)) {
        $this->createDedicatedTableSchema($field_storage_definition);
      }
      elseif ($table_mapping->allowsSharedTableStorage($field_storage_definition)) {
        // The shared tables are already fully created, but we need to save the
        // per-field schema definitions for later use.
        $this->createSharedTableSchema($field_storage_definition, TRUE);
      }
    }

    // Save data about entity indexes and keys.
    $this->saveEntitySchemaData($entity_type, $schema);
  }

  /**
   * {@inheritdoc}
   */
  public function onEntityTypeDelete(EntityTypeInterface $entity_type) {
    $this->checkEntityType($entity_type);
    $schema_handler = $this->database->schema();

    // Delete entity base table. Deleting the base table also deletes all
    // embedded tables.
    if ($schema_handler->tableExists($this->storage->getBaseTable())) {
      $schema_handler->dropTable($this->storage->getBaseTable());
    }

    // Delete dedicated field tables.
    $table_mapping = $this->getTableMapping($entity_type, $this->fieldStorageDefinitions);
    foreach ($this->fieldStorageDefinitions as $field_storage_definition) {
      // If we have a field having dedicated storage we need to drop it,
      // otherwise we just remove the related schema data.
      if ($table_mapping->requiresDedicatedTableStorage($field_storage_definition)) {
        $this->deleteDedicatedTableSchema($field_storage_definition);
      }
      elseif ($table_mapping->allowsSharedTableStorage($field_storage_definition)) {
        $this->deleteFieldSchemaData($field_storage_definition);
      }
    }

    // Delete the entity schema.
    $this->deleteEntitySchemaData($entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldStorageDefinitionDelete(FieldStorageDefinitionInterface $storage_definition) {
    try {
      $has_data = $this->storage->countFieldData($storage_definition, TRUE);
    }
    catch (DatabaseExceptionWrapper $e) {
      // This may happen when changing field storage schema, since we are not
      // able to use a table mapping matching the passed storage definition.
      // @todo Revisit this once we are able to instantiate the table mapping
      //   properly. See https://www.drupal.org/node/2274017.
      return;
    }

    // If the field storage does not have any data, we can safely delete its
    // schema.
    if (!$has_data) {
      $this->performFieldSchemaOperation('delete', $storage_definition);
      return;
    }

    // There's nothing else we can do if the field storage has a custom storage.
    if ($storage_definition->hasCustomStorage()) {
      return;
    }

    // Retrieve a table mapping which contains the deleted field still.
    $table_mapping = $this->getTableMapping($this->entityType, [$storage_definition]);
    if ($table_mapping->requiresDedicatedTableStorage($storage_definition)) {
      $base_table = $this->storage->getBaseTable();
      $prefixed_table = $this->database->getMongodbPrefixedTable($base_table);
      $schema = $this->getDedicatedTableSchema($storage_definition);
      $id_key = $this->entityType->getKey('id');

      // Move the table to a unique name while the table contents are being
      // deleted.
      if ($this->entityType->isRevisionable()) {
        // For MongoDB: All embedded table data needs to be renamed.
        $all_revisions_table = $this->storage->getAllRevisionsTable();
        $dedicated_all_revisions_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $all_revisions_table);
        $dedicated_all_revisions_new_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $all_revisions_table, TRUE);
        $this->database->schema()->createEmbeddedTable($all_revisions_table, $dedicated_all_revisions_new_table, $schema[$dedicated_all_revisions_table]);

        $current_revision_table = $this->storage->getCurrentRevisionTable();
        $dedicated_current_revision_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $current_revision_table);
        $dedicated_current_revision_new_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $current_revision_table, TRUE);
        // Check if there already exists a table with that name. If so, then delete it.
        if ($this->database->schema()->tableExists($dedicated_current_revision_new_table)) {
          $this->database->schema()->dropTable($dedicated_current_revision_new_table);
        }
        $this->database->schema()->createEmbeddedTable($current_revision_table, $dedicated_current_revision_new_table, $schema[$dedicated_current_revision_table]);

        $latest_revision_table = $this->storage->getLatestRevisionTable();
        $dedicated_latest_revision_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $latest_revision_table);
        $dedicated_latest_revision_new_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $latest_revision_table, TRUE);
        // Check if there already exists a table with that name. If so, then delete it.
        if ($this->database->schema()->tableExists($dedicated_latest_revision_new_table)) {
          $this->database->schema()->dropTable($dedicated_latest_revision_new_table);
        }
        $this->database->schema()->createEmbeddedTable($latest_revision_table, $dedicated_latest_revision_new_table, $schema[$dedicated_latest_revision_table]);

        $this->database->schema()->dropTable($dedicated_all_revisions_table);
        $this->database->schema()->dropTable($dedicated_current_revision_table);
        $this->database->schema()->dropTable($dedicated_latest_revision_table);

        $cursor = $this->database->getConnection()->{$prefixed_table}->find(
          [ "$all_revisions_table.$dedicated_all_revisions_table" => [ '$exists' => TRUE ]],
          [ 'projection' => [$id_key => 1, $all_revisions_table => 1, $current_revision_table => 1, $latest_revision_table => 1, '_id' => 0]]
        );

        foreach ($cursor as $entity) {
          if (isset($entity->{$all_revisions_table})) {
            foreach ($entity->{$all_revisions_table} as &$revision) {
              if (isset($revision[$dedicated_all_revisions_table])) {
                $revision[$dedicated_all_revisions_new_table] = $revision[$dedicated_all_revisions_table];
                unset($revision[$dedicated_all_revisions_table]);
              }
            }
          }

          if (isset($entity->{$current_revision_table})) {
            foreach ($entity->{$current_revision_table} as &$revision) {
              if (isset($revision[$dedicated_current_revision_table])) {
                $revision[$dedicated_current_revision_new_table] = $revision[$dedicated_current_revision_table];
                unset($revision[$dedicated_current_revision_table]);
              }
            }
          }

          if (isset($entity->{$latest_revision_table})) {
            foreach ($entity->{$latest_revision_table} as &$revision) {
              if (isset($revision[$dedicated_latest_revision_table])) {
                $revision[$dedicated_latest_revision_new_table] = $revision[$dedicated_latest_revision_table];
                unset($revision[$dedicated_latest_revision_table]);
              }
            }
          }

          $result = $this->database->getConnection()->{$prefixed_table}->updateMany(
            [ $id_key => $entity->{$id_key} ],
            [ '$set' => [
              $all_revisions_table => $entity->{$all_revisions_table},
              $current_revision_table => $entity->{$current_revision_table},
              $latest_revision_table => $entity->{$latest_revision_table}
            ]]
          );
        }
      }
      elseif ($this->entityType->isTranslatable()) {
        // For MongoDB: All embedded table data needs to be renamed.
        $translations_table = $this->storage->getTranslationsTable();
        $dedicated_translations_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $translations_table);
        $dedicated_translations_new_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $translations_table, TRUE);
        $this->database->schema()->createEmbeddedTable($translations_table, $dedicated_translations_new_table, $schema[$dedicated_translations_table]);
        $this->database->schema()->dropTable($dedicated_translations_table);

        $cursor = $this->database->getConnection()->{$prefixed_table}->find(
          [ "$translations_table.$dedicated_translations_table" => [ '$exists' => TRUE ]],
          [ 'projection' => [$id_key => 1, $translations_table => 1, '_id' => 0]]
        );

        foreach ($cursor as $entity) {
          if (isset($entity->{$translations_table})) {
            foreach ($entity->{$translations_table} as &$revision) {
              if (isset($revision[$dedicated_translations_table])) {
                $revision[$dedicated_translations_new_table] = $revision[$dedicated_translations_table];
                unset($revision[$dedicated_translations_table]);
              }
            }
          }

          $result = $this->database->getConnection()->{$prefixed_table}->updateMany(
            [ $id_key => $entity->{$id_key} ],
            [ '$set' => [
              $translations_table => $entity->{$translations_table}
            ]]
          );
        }
      }
      else {
        // For MongoDB: All embedded table data needs to be renamed.
        $base_table = $this->storage->getBaseTable();
        $dedicated_base_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $base_table);
        $dedicated_base_new_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $base_table, TRUE);

        // Delete the old archived table before renaming or the renaming will fail. Two tables cannot have the same name.
        if ($this->database->schema()->tableExists($dedicated_base_new_table)) {
          $this->database->schema()->dropTable($dedicated_base_new_table);
        }
        $this->database->schema()->renameTable($dedicated_base_table, $dedicated_base_new_table);
      }
    }
    else {
      // Move the field data from the shared table to a dedicated one in order
      // to allow it to be purged like any other field.
      $shared_table_field_columns = $table_mapping->getColumnNames($storage_definition->getName());
      foreach ($shared_table_field_columns as $shared_table_field_column) {
        if ($this->entityType->isRevisionable()) {
          $all_revisions_table = $table_mapping->getAllRevisionsTable();
          if ($this->database->schema()->fieldExists($all_revisions_table, $shared_table_field_column)) {
            $this->database->schema()->dropField($all_revisions_table, $shared_table_field_column);
          }
          $current_revision_table = $table_mapping->getCurrentRevisionTable();
          if ($this->database->schema()->fieldExists($current_revision_table, $shared_table_field_column)) {
            $this->database->schema()->dropField($current_revision_table, $shared_table_field_column);
          }
          $latest_revision_table = $table_mapping->getLatestRevisionTable();
          if ($this->database->schema()->fieldExists($latest_revision_table, $shared_table_field_column)) {
            $this->database->schema()->dropField($latest_revision_table, $shared_table_field_column);
          }
        }
        elseif ($this->entityType->isTranslatable()) {
          $translations_table = $table_mapping->getTranslationsTable();
          if ($this->database->schema()->fieldExists($translations_table, $shared_table_field_column)) {
            $this->database->schema()->dropField($translations_table, $shared_table_field_column);
          }
        }
        $base_table = $table_mapping->getBaseTable();
        if ($this->database->schema()->fieldExists($base_table, $shared_table_field_column)) {
          $this->database->schema()->dropField($base_table, $shared_table_field_column);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $this->checkEntityType($entity_type);
    $entity_type_id = $entity_type->id();

    if (!isset($this->schema[$entity_type_id]) || $reset) {
      // Prepare basic information about the entity type.
      /** @var \Drupal\Core\Entity\Sql\DefaultTableMapping $table_mapping */
      $table_mapping = $this->getTableMapping($entity_type, $this->fieldStorageDefinitions);
      $tables = $this->getEntitySchemaTables($table_mapping);

      // Initialize the table schema.
      $schema[$tables['base_table']] = $this->initializeBaseTable($entity_type);

      // Initialize the table schema.
      if (isset($tables['all_revisions_table'])) {
        $schema[$tables['all_revisions_table']] = $this->initializeRevisionsTable($entity_type);
      }
      if (isset($tables['current_revision_table'])) {
        $schema[$tables['current_revision_table']] = $this->initializeRevisionsTable($entity_type);
      }
      if (isset($tables['latest_revision_table'])) {
        $schema[$tables['latest_revision_table']] = $this->initializeRevisionsTable($entity_type);
      }
      if (isset($tables['translations_table'])) {
        $schema[$tables['translations_table']] = $this->initializeTranslationsTable($entity_type);
      }

      // We need to act only on shared entity schema tables.
//      $table_mapping = $this->storage->getTableMapping();
      $table_names = array_diff($table_mapping->getTableNames(), $table_mapping->getDedicatedTableNames());
      foreach ($table_names as $table_name) {
        if (!isset($schema[$table_name])) {
          $schema[$table_name] = [];
        }
        foreach ($table_mapping->getFieldNames($table_name) as $field_name) {
          if (!isset($this->fieldStorageDefinitions[$field_name])) {
            throw new FieldException("Field storage definition for '$field_name' could not be found.");
          }
          // Add the schema for base field definitions.
          elseif ($table_mapping->allowsSharedTableStorage($this->fieldStorageDefinitions[$field_name])) {
            $column_names = $table_mapping->getColumnNames($field_name);
            $storage_definition = $this->fieldStorageDefinitions[$field_name];
            $schema[$table_name] = array_merge_recursive($schema[$table_name], $this->getSharedTableFieldSchema($storage_definition, $table_name, $column_names));
          }
        }
      }

      // Process tables after having gathered field information.
// Not sure why the next method has been removed.
//      $this->processBaseTable($entity_type, $schema[$tables['base_table']]);

      if (isset($tables['all_revisions_table'])) {
        $this->processRevisionsTable($entity_type, $schema[$tables['all_revisions_table']]);
      }
      if (isset($tables['current_revision_table'])) {
        $this->processRevisionsTable($entity_type, $schema[$tables['current_revision_table']]);
      }
      if (isset($tables['latest_revision_table'])) {
        $this->processRevisionsTable($entity_type, $schema[$tables['latest_revision_table']]);
      }
//      if (isset($tables['translations_table'])) {
//        $this->processTranslationsTable($entity_type, $schema[$tables['translations_table']]);
//      }

      $this->schema[$entity_type_id] = $schema;
    }

    return $this->schema[$entity_type_id];
  }

  /**
   * {@inheritdoc}
   *
  protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, $table_name, array $column_mapping) {
    $schema = parent::getSharedTableFieldSchema($storage_definition, $table_name, $column_mapping);

    $all_revisions_table = $this->storage->getAllRevisionsTable();
    $current_revision_table = $this->storage->getCurrentRevisionTable();
    $latest_revision_table = $this->storage->getLatestRevisionTable();

    // Process the 'id' and 'revision' entity keys for the base and revision
    // tables.
    if (in_array($table_name, [$all_revisions_table, $current_revision_table, $latest_revision_table], TRUE) &&
      ($field_name === $this->entityType->getKey('revision'))) {
      $this->processIdentifierSchema($schema, $field_name);
    }

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchemaTables(TableMappingInterface $table_mapping) {
    return array_filter([
      'base_table' => $table_mapping->getBaseTable(),
      'all_revisions_table' => $table_mapping->getAllRevisionsTable(),
      'current_revision_table' => $table_mapping->getCurrentRevisionTable(),
      'latest_revision_table' => $table_mapping->getLatestRevisionTable(),
      'translations_table' => $table_mapping->getTranslationsTable(),
    ]);
  }

  /**
   * Initializes common information for a MongoDB all revisions table.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return array
   *   A partial schema array for the all revisions table.
   */
  protected function initializeRevisionsTable(ContentEntityTypeInterface $entity_type) {
    $entity_type_id = $entity_type->id();
    $id_key = $entity_type->getKey('id');
    $revision_key = $entity_type->getKey('revision');

    $schema = array(
      'description' => "The all revisions table for $entity_type_id entities.",
      'indexes' => [],
    );

    if ($entity_type->isTranslatable()) {
      $schema['primary key'] = array($revision_key, $entity_type->getKey('langcode'));
    }
    else {
      $schema['primary key'] = array($revision_key);
    }

    $this->addTableDefaults($schema);

    return $schema;
  }

  /**
   * Initializes common information for a MongoDB translations table.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return array
   *   A partial schema array for the translations table.
   */
  protected function initializeTranslationsTable(ContentEntityTypeInterface $entity_type) {
    $entity_type_id = $entity_type->id();
    $id_key = $entity_type->getKey('id');

    $schema = array(
      'description' => "The translations table for $entity_type_id entities.",
      'indexes' => [],
    );

    $schema['primary key'] = array($entity_type->getKey('id'), $entity_type->getKey('langcode'));

    $this->addTableDefaults($schema);

    return $schema;
  }

  /**
   * Processes the gathered schema for a embedded all revisions table.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The entity type.
   * @param array $schema
   *   The table schema, passed by reference.
   *
   * @return array
   *   A partial schema array for the all revisions table.
   */
  protected function processRevisionsTable(ContentEntityTypeInterface $entity_type, array &$schema) {
    // Change the field "revision_id" from serial to integer. Serial primary key
    // fields are auto-incremented. This is something we do not want from an
    // embedded table.
    if ($entity_type->hasKey('revision')) {
      $schema['fields'][$entity_type->getKey('revision')]['type'] = 'int';
    }
  }

  /**
   * Processes the gathered schema for a embedded translations table.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The entity type.
   * @param array $schema
   *   The table schema, passed by reference.
   *
   * @return array
   *   A partial schema array for the translations table.
   */
  protected function processTranslationsTable(ContentEntityTypeInterface $entity_type, array &$schema) {}

  /**
   * {@inheritdoc}
   */
  protected function createDedicatedTableSchema(FieldStorageDefinitionInterface $storage_definition, $only_save = false) {
    $table_mapping = $this->getTableMapping($this->entityType, [$storage_definition]);
    $schema = $this->getDedicatedTableSchema($storage_definition);

    if (!$only_save) {
      foreach ($this->getEntitySchemaTables($table_mapping) as $table_name) {
        $dedicated_table_name = $table_mapping->getMongodbDedicatedTableName($storage_definition, $table_name);
        if (isset($schema[$dedicated_table_name])) {
          // Check if the table exists because it might already have been
          // created as part of the earlier entity type update event.
          if ($this->database->schema()->tableExists($dedicated_table_name)) {
            $this->database->schema()->dropTable($dedicated_table_name);
          }

          $this->database->schema()->createEmbeddedTable($table_name, $dedicated_table_name, $schema[$dedicated_table_name]);
        }
      }
    }

    $this->saveFieldSchemaData($storage_definition, $schema);
  }

  /**
   * {@inheritdoc}
   */
  protected function deleteDedicatedTableSchema(FieldStorageDefinitionInterface $storage_definition) {
    // When switching from dedicated to shared field table layout we need need
    // to delete the field tables with their regular names. When this happens
    // original definitions will be defined.
    $table_mapping = $this->getTableMapping($this->entityType, [$storage_definition]);
    if ($all_revisions_table = $table_mapping->getAllRevisionsTable()) {
      $dedicated_all_revisions_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $all_revisions_table, $storage_definition->isDeleted());
      if ($this->database->schema()->tableExists($dedicated_all_revisions_table)) {
        $this->database->schema()->dropTable($dedicated_all_revisions_table);
      }
    }

    if ($current_revision_table = $table_mapping->getCurrentRevisionTable()) {
      $dedicated_current_revision_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $current_revision_table, $storage_definition->isDeleted());
      if ($this->database->schema()->tableExists($dedicated_current_revision_table)) {
        $this->database->schema()->dropTable($dedicated_current_revision_table);
      }
    }

    if ($latest_revision_table = $table_mapping->getLatestRevisionTable()) {
      $dedicated_latest_revision_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $latest_revision_table, $storage_definition->isDeleted());
      if ($this->database->schema()->tableExists($dedicated_latest_revision_table)) {
        $this->database->schema()->dropTable($dedicated_latest_revision_table);
      }
    }

    if ($translations_table = $table_mapping->getTranslationsTable()) {
      $dedicated_translations_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $translations_table, $storage_definition->isDeleted());
      if ($this->database->schema()->tableExists($dedicated_translations_table)) {
        $this->database->schema()->dropTable($dedicated_translations_table);
      }
    }

    if (!$this->entityType->isRevisionable() && !$this->entityType->isTranslatable()) {
      $dedicated_base_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $table_mapping->getBaseTable(), $storage_definition->isDeleted());
      if ($this->database->schema()->tableExists($dedicated_base_table)) {
        $this->database->schema()->dropTable($dedicated_base_table);
      }
    }

    $this->deleteFieldSchemaData($storage_definition);
  }

  /**
   * {@inheritdoc}
   */
  protected function deleteSharedTableSchema(FieldStorageDefinitionInterface $storage_definition) {
    // Make sure any entity index involving this field is dropped.
    $this->deleteEntitySchemaIndexes($this->loadEntitySchemaData($this->entityType), $storage_definition);

    $deleted_field_name = $storage_definition->getName();
    $table_mapping = $this->getTableMapping($this->entityType, [$storage_definition]);
    $column_names = $table_mapping->getColumnNames($deleted_field_name);
    $schema_handler = $this->database->schema();
    $shared_table_names = array_diff($table_mapping->getTableNames(), $table_mapping->getDedicatedTableNames());

    // Iterate over the mapped table to find the ones that host the deleted
    // field schema.
    foreach ($shared_table_names as $table_name) {
      foreach ($table_mapping->getFieldNames($table_name) as $field_name) {
        if ($field_name == $deleted_field_name) {
          $schema = $this->getSharedTableFieldSchema($storage_definition, $table_name, $column_names);

          // Drop indexes and unique keys first.
          if (!empty($schema['indexes'])) {
            foreach ($schema['indexes'] as $name => $specifier) {
              $schema_handler->dropIndex($table_name, $name);
            }
          }
          if (!empty($schema['unique keys'])) {
            foreach ($schema['unique keys'] as $name => $specifier) {
              $schema_handler->dropUniqueKey($table_name, $name);
            }
          }

          // Drop columns.
          foreach ($column_names as $column_name) {
            $schema_handler->dropField($table_name, $column_name);
          }

          // After deleting the field schema skip to the next table.
          break;
        }
      }
    }

    $this->deleteFieldSchemaData($storage_definition);
  }

  /**
   * {@inheritdoc}
   */
  protected function updateDedicatedTableSchema(FieldStorageDefinitionInterface $storage_definition, FieldStorageDefinitionInterface $original) {
    if (!$this->storage->countFieldData($original, TRUE)) {
      // There is no data. Re-create the tables completely.
      if ($this->database->supportsTransactionalDDL()) {
        // If the database supports transactional DDL, we can go ahead and rely
        // on it. If not, we will have to rollback manually if something fails.
        $transaction = $this->database->startTransaction();
      }
      try {
        // Since there is no data we may be switching from a shared table schema
        // to a dedicated table schema, hence we should use the proper API.
        $this->performFieldSchemaOperation('delete', $original);
        $this->performFieldSchemaOperation('create', $storage_definition);
      }
      catch (\Exception $e) {
        if ($this->database->supportsTransactionalDDL()) {
          $transaction->rollback();
        }
        else {
          // Recreate tables.
          $this->performFieldSchemaOperation('create', $original);
        }
        throw $e;
      }
    }
    else {
      if ($this->hasColumnChanges($storage_definition, $original)) {
        throw new FieldStorageDefinitionUpdateForbiddenException('The SQL storage cannot change the schema for an existing field (' . $storage_definition->getName() . ' in ' . $storage_definition->getTargetEntityTypeId() . ' entity) with data.');
      }
      // There is data, so there are no column changes. Drop all the prior
      // indexes and create all the new ones, except for all the priors that
      // exist unchanged.
      $table_mapping = $this->getTableMapping($this->entityType, [$storage_definition]);
      if ($this->entityType->isRevisionable()) {
        $dedicated_all_revisions_table = $table_mapping->getMongodbDedicatedTableName($original, $this->storage->getAllRevisionsTable());
        $dedicated_current_revision_table = $table_mapping->getMongodbDedicatedTableName($original, $this->storage->getCurrentRevisionTable());
        $dedicated_latest_revision_table = $table_mapping->getMongodbDedicatedTableName($original, $this->storage->getLatestRevisionTable());
      }
      elseif ($this->entityType->isTranslatable()) {
        $dedicated_translations_table = $table_mapping->getMongodbDedicatedTableName($original, $this->storage->getTranslationsTable());
      }
      else {
        $dedicated_base_table = $table_mapping->getMongodbDedicatedTableName($original, $this->storage->getBaseTable());
      }

      // Get the field schemas.
      $schema = $storage_definition->getSchema();
      $original_schema = $original->getSchema();

      // Gets the SQL schema for a dedicated tables.
      $actual_schema = $this->getDedicatedTableSchema($storage_definition);

      foreach ($original_schema['indexes'] as $name => $columns) {
        if (!isset($schema['indexes'][$name]) || $columns != $schema['indexes'][$name]) {
          $real_name = $this->getFieldIndexName($storage_definition, $name);
          if ($this->entityType->isRevisionable()) {
            $this->database->schema()->dropIndex($dedicated_all_revisions_table, $real_name);
            $this->database->schema()->dropIndex($dedicated_current_revision_table, $real_name);
            $this->database->schema()->dropIndex($dedicated_latest_revision_table, $real_name);
          }
          elseif ($this->entityType->isTranslatable()) {
            $this->database->schema()->dropIndex($dedicated_translations_table, $real_name);
          }
          else {
            $this->database->schema()->dropIndex($dedicated_base_table, $real_name);
          }
        }
      }

      if ($this->entityType->isRevisionable()) {
        $dedicated_all_revisions_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->storage->getAllRevisionsTable());
        $dedicated_current_revision_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->storage->getCurrentRevisionTable());
        $dedicated_latest_revision_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->storage->getLatestRevisionTable());
      }
      elseif ($this->entityType->isTranslatable()) {
        $dedicated_translations_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->storage->getTranslationsTable());
      }
      else {
        $dedicated_base_table = $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->storage->getBaseTable());
      }
      foreach ($schema['indexes'] as $name => $columns) {
        if (!isset($original_schema['indexes'][$name]) || $columns != $original_schema['indexes'][$name]) {
          $real_name = $this->getFieldIndexName($storage_definition, $name);
          $real_columns = [];
          foreach ($columns as $column_name) {
            // Indexes can be specified as either a column name or an array with
            // column name and length. Allow for either case.
            if (is_array($column_name)) {
              // MongoDB cannot do anything with the length parameter.
              $real_columns[] = $table_mapping->getFieldColumnName($storage_definition, reset($column_name));
            }
            else {
              $real_columns[] = $table_mapping->getFieldColumnName($storage_definition, $column_name);
            }
          }
          if ($this->entityType->isRevisionable()) {
            $this->addIndex($dedicated_all_revisions_table, $real_name, $real_columns, $actual_schema[$dedicated_all_revisions_table]);
            $this->addIndex($dedicated_current_revision_table, $real_name, $real_columns, $actual_schema[$dedicated_current_revision_table]);
            $this->addIndex($dedicated_latest_revision_table, $real_name, $real_columns, $actual_schema[$dedicated_latest_revision_table]);
          }
          elseif ($this->entityType->isTranslatable()) {
            $this->addIndex($dedicated_translations_table, $real_name, $real_columns, $actual_schema[$dedicated_translations_table]);
          }
          else {
            $this->addIndex($dedicated_base_table, $real_name, $real_columns, $actual_schema[$dedicated_base_table]);
          }
        }
      }
      $this->saveFieldSchemaData($storage_definition, $this->getDedicatedTableSchema($storage_definition));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getDedicatedTableSchema(FieldStorageDefinitionInterface $storage_definition, ContentEntityTypeInterface $entity_type = NULL) {
    $id_definition = $this->fieldStorageDefinitions[$this->entityType->getKey('id')];
    if ($id_definition->getType() == 'integer') {
      $id_schema = array(
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'description' => 'The entity id this data is attached to',
      );
    }
    else {
      $id_schema = array(
        'type' => 'varchar_ascii',
        'length' => 128,
        'not null' => TRUE,
        'description' => 'The entity id this data is attached to',
      );
    }

    // Define the revision ID schema.
    if (!$this->entityType->isRevisionable()) {
      $revision_id_schema = $id_schema;
      $revision_id_schema['description'] = 'The entity revision id this data is attached to, which for an unversioned entity type is the same as the entity id';
    }
    elseif ($this->fieldStorageDefinitions[$this->entityType->getKey('revision')]->getType() == 'integer') {
      $revision_id_schema = array(
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'description' => 'The entity revision id this data is attached to',
      );
    }
    else {
      $revision_id_schema = array(
        'type' => 'varchar',
        'length' => 128,
        'not null' => TRUE,
        'description' => 'The entity revision id this data is attached to',
      );
    }

    $dedicated_schema = array(
      'fields' => array(
        'bundle' => array(
          'type' => 'varchar_ascii',
          'length' => 128,
          'not null' => TRUE,
          'default' => '',
          'description' => 'The field instance bundle to which this row belongs, used when deleting a field instance',
        ),
        'deleted' => array(
          'type' => 'bool',
          'not null' => TRUE,
          'default' => FALSE,
          'description' => 'A boolean indicating whether this data item has been deleted'
        ),
        'entity_id' => $id_schema,
        'revision_id' => $revision_id_schema,
        'langcode' => array(
          'type' => 'varchar_ascii',
          'length' => 32,
          'not null' => TRUE,
          'default' => '',
          'description' => 'The language code for this data item.',
        ),
        'delta' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'description' => 'The sequence number for this data item, used for multi-value fields',
        ),
      ),
      'indexes' => array(
        'bundle' => array('bundle'),
        'revision_id' => array('revision_id'),
      ),
    );

    // Check that the schema does not include forbidden column names.
    $schema = $storage_definition->getSchema();
    $properties = $storage_definition->getPropertyDefinitions();
    $table_mapping = $this->getTableMapping($this->entityType, [$storage_definition]);
    if (array_intersect(array_keys($schema['columns']), $table_mapping->getReservedColumns())) {
      throw new FieldException("Illegal field column names on {$storage_definition->getName()}");
    }

    // Add field columns.
    foreach ($schema['columns'] as $column_name => $attributes) {
      $real_name = $table_mapping->getFieldColumnName($storage_definition, $column_name);
      $dedicated_schema['fields'][$real_name] = $attributes;
      // A dedicated table only contain rows for actual field values, and no
      // rows for entities where the field is empty. Thus, we can safely
      // enforce 'not null' on the columns for the field's required properties.
      $dedicated_schema['fields'][$real_name]['not null'] = $properties[$column_name]->isRequired();
    }

    // Add indexes.
    foreach ($schema['indexes'] as $index_name => $columns) {
      $real_name = $this->getFieldIndexName($storage_definition, $index_name);
      foreach ($columns as $column_name) {
        // Indexes can be specified as either a column name or an array with
        // column name and length. Allow for either case.
        if (is_array($column_name)) {
          $dedicated_schema['indexes'][$real_name][] = array(
            $table_mapping->getFieldColumnName($storage_definition, $column_name[0]),
            $column_name[1],
          );
        }
        else {
          $dedicated_schema['indexes'][$real_name][] = $table_mapping->getFieldColumnName($storage_definition, $column_name);
        }
      }
    }

    // Add unique keys.
    foreach ($schema['unique keys'] as $index_name => $columns) {
      $real_name = $this->getFieldIndexName($storage_definition, $index_name);
      foreach ($columns as $column_name) {
        // Unique keys can be specified as either a column name or an array with
        // column name and length. Allow for either case.
        if (is_array($column_name)) {
          $dedicated_schema['unique keys'][$real_name][] = array(
            $table_mapping->getFieldColumnName($storage_definition, $column_name[0]),
            $column_name[1],
          );
        }
        else {
          $dedicated_schema['unique keys'][$real_name][] = $table_mapping->getFieldColumnName($storage_definition, $column_name);
        }
      }
    }

    // Add foreign keys.
    foreach ($schema['foreign keys'] as $specifier => $specification) {
      $real_name = $this->getFieldIndexName($storage_definition, $specifier);
      $dedicated_schema['foreign keys'][$real_name]['table'] = $specification['table'];
      foreach ($specification['columns'] as $column_name => $referenced) {
        $sql_storage_column = $table_mapping->getFieldColumnName($storage_definition, $column_name);
        $dedicated_schema['foreign keys'][$real_name]['columns'][$sql_storage_column] = $referenced;
      }
    }

    // TODO: Removing the added indexes. No doing so can result in the error:
    // "too many indexes".
    unset($dedicated_schema['primary key']);
    unset($dedicated_schema['unique keys']);
    unset($dedicated_schema['indexes']);

    $entity_type = $entity_type ?: $this->entityType;
    if ($entity_type->isRevisionable()) {
      // Adding an index for every field can create too many indezes on a single
      // table. For MongoDB the maximum is 64.
      //$dedicated_schema['indexes']['primary_key'] = ['entity_id', 'revision_id', 'deleted', 'delta', 'langcode'];
      $dedicated_schema['fields']['revision_id']['not null'] = TRUE;
      $dedicated_schema['fields']['revision_id']['description'] = 'The entity revision id this data is attached to';

      $dedicated_all_revisions_schema = $dedicated_schema;
      $dedicated_all_revisions_schema['description'] = "Revision archive storage for {$storage_definition->getTargetEntityTypeId()} field {$storage_definition->getName()}.";

      $dedicated_current_revision_schema = $dedicated_schema;
      $dedicated_current_revision_schema['description'] = "Current revision storage for {$storage_definition->getTargetEntityTypeId()} field {$storage_definition->getName()}.";

      $dedicated_latest_revision_schema = $dedicated_schema;
      $dedicated_latest_revision_schema['description'] = "Latest revision storage for {$storage_definition->getTargetEntityTypeId()} field {$storage_definition->getName()}.";

      return [
        $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->storage->getAllRevisionsTable()) => $dedicated_all_revisions_schema,
        $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->storage->getCurrentRevisionTable()) => $dedicated_current_revision_schema,
        $table_mapping->getMongodbDedicatedTableName($storage_definition, $this->storage->getLatestRevisionTable()) => $dedicated_latest_revision_schema
      ];
    }
    elseif ($entity_type->isTranslatable()) {
      // Adding an index for every field can create too many indezes on a single
      // table. For MongoDB the maximum is 64.
      //$dedicated_schema['indexes']['primary_key'] = ['entity_id', 'deleted', 'delta', 'langcode'];
      $dedicated_schema['description'] = "Translations storage for {$storage_definition->getTargetEntityTypeId()} field {$storage_definition->getName()}.";

      return [$table_mapping->getMongodbDedicatedTableName($storage_definition, $this->storage->getTranslationsTable()) => $dedicated_schema];
    }
    else {
      // Adding an index for every field can create too many indezes on a single
      // table. For MongoDB the maximum is 64.
      //$dedicated_schema['indexes']['primary_key'] = ['entity_id', 'deleted', 'delta'];
      $dedicated_schema['description'] = "Storage for {$storage_definition->getTargetEntityTypeId()} field {$storage_definition->getName()}.";

      return [$table_mapping->getMongodbDedicatedTableName($storage_definition, $entity_type->getBaseTable()) => $dedicated_schema];
    }
  }

}
