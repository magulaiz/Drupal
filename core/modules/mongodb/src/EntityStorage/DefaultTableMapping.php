<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\DefaultTableMapping as CoreDefaultTableMapping;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageException;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\views\ViewsConfigUpdater;

/**
 * The MongoDB implementation of \Drupal\Core\Entity\Sql\DefaultTableMapping.
 */
class DefaultTableMapping extends CoreDefaultTableMapping {

  /**
   * The table that stores the all revisions data for the entity.
   *
   * @var string
   */
  protected $allRevisionsTable;

  /**
   * The table that stores the current revision data for the entity.
   *
   * @var string
   */
  protected $currentRevisionTable;

  /**
   * The table that stores the latest revision data for the entity.
   *
   * @var string
   */
  protected $latestRevisionTable;

  /**
   * The table that stores the translations data for the entity.
   *
   * @var string
   */
  protected $translationsTable;

  /**
   * {@inheritdoc}
   */
  public function __construct(ContentEntityTypeInterface $entity_type, array $storage_definitions, $prefix = '') {
    $this->entityType = $entity_type;
    $this->fieldStorageDefinitions = $storage_definitions;
    $this->prefix = $prefix;

    $this->baseTable = $this->prefix . $entity_type->getBaseTable() ?: $entity_type->id();
    if ($entity_type->isRevisionable()) {
      $this->allRevisionsTable = $this->prefix . $entity_type->id() . '_all_revisions';
      $this->currentRevisionTable = $this->prefix . $entity_type->id() . '_current_revision';
      $this->latestRevisionTable = $this->prefix . $entity_type->id() . '_latest_revision';
    }
    elseif ($entity_type->isTranslatable()) {
      $this->translationsTable = $this->prefix . $entity_type->id() . '_translations';
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContentEntityTypeInterface $entity_type, array $storage_definitions, $prefix = '', bool $json_storage = FALSE) {
    $table_mapping = new static($entity_type, $storage_definitions, $prefix);

    $revisionable = $entity_type->isRevisionable();
    $translatable = $entity_type->isTranslatable();

    $id_key = $entity_type->getKey('id');
    $revision_key = $entity_type->getKey('revision');
    $bundle_key = $entity_type->getKey('bundle');
    $uuid_key = $entity_type->getKey('uuid');
    $langcode_key = $entity_type->getKey('langcode');

    $shared_table_definitions = array_filter($storage_definitions, function (FieldStorageDefinitionInterface $definition) use ($table_mapping) {
      return $table_mapping->allowsSharedTableStorage($definition);
    });

    $key_fields = array_values(array_filter([$id_key, $revision_key, $bundle_key, $uuid_key, $langcode_key]));
    $all_fields = array_keys($shared_table_definitions);
    // $revisionable_fields = array_keys(array_filter($shared_table_definitions, function (FieldStorageDefinitionInterface $definition) {
    // return $definition->isRevisionable();
    // }));
    // Make sure the key fields come first in the list of fields.
    $all_fields = array_merge($key_fields, array_diff($all_fields, $key_fields));

    // $revision_metadata_fields = $revisionable ? array_values($entity_type->getRevisionMetadataKeys()) : [];

    if (!$revisionable && !$translatable) {
      // The base layout stores all the base field values in the base table.
      $table_mapping->setFieldNames($table_mapping->baseTable, $all_fields);
    }
    elseif ($revisionable && !$translatable) {
      $non_revisionable_fields = array_keys(array_filter($shared_table_definitions, function (FieldStorageDefinitionInterface $definition) {
        return !$definition->isRevisionable();
      }));
      $non_revisionable_fields = array_diff(array_values($non_revisionable_fields), $key_fields);

      // The entity_type is not translatable, so the $langcode_key can be removed.
      $key_fields = array_diff($key_fields, [$langcode_key]);

      $table_mapping->setFieldNames($table_mapping->baseTable, array_merge($key_fields, $non_revisionable_fields));
    }
    elseif (!$revisionable && $translatable) {
      $non_translatable_fields = array_keys(array_filter($shared_table_definitions, function (FieldStorageDefinitionInterface $definition) {
        return !$definition->isTranslatable();
      }));
      $non_translatable_fields = array_diff(array_values($non_translatable_fields), $key_fields);

      $table_mapping->setFieldNames($table_mapping->baseTable, array_merge($key_fields, $non_translatable_fields));
    }
    elseif ($revisionable && $translatable) {
      $non_revisionable_non_translatable_fields = array_keys(array_filter($shared_table_definitions, function (FieldStorageDefinitionInterface $definition) {
        return !$definition->isRevisionable();
      }));
      $non_revisionable_non_translatable_fields = array_diff(array_values($non_revisionable_non_translatable_fields), $key_fields);

      $table_mapping->setFieldNames($table_mapping->baseTable, array_merge($key_fields, $non_revisionable_non_translatable_fields));
    }

    // Add dedicated tables.
    $dedicated_table_definitions = array_filter($table_mapping->fieldStorageDefinitions, function (FieldStorageDefinitionInterface $definition) use ($table_mapping) {
      return $table_mapping->requiresDedicatedTableStorage($definition);
    });
    $extra_columns = [
      'bundle',
      'deleted',
      'entity_id',
      'revision_id',
      'langcode',
      'delta',
    ];

    // The MongoDB table mapping.
    // Add all fields to all embedded tables, this makes EntityQuery happy!
    if ($revisionable) {
      $table_mapping->setFieldNames($table_mapping->allRevisionsTable, $all_fields);
      $table_mapping->setFieldNames($table_mapping->currentRevisionTable, $all_fields);
      $table_mapping->setFieldNames($table_mapping->latestRevisionTable, $all_fields);
    }
    elseif ($translatable) {
      $table_mapping->setFieldNames($table_mapping->translationsTable, $all_fields);
    }

    foreach ($dedicated_table_definitions as $field_name => $definition) {
      $tables = [];
      if ($table_mapping->currentRevisionTable) {
        $tables[] = $table_mapping->getMongodbDedicatedTableName($definition, $table_mapping->currentRevisionTable);
      }
      if ($table_mapping->translationsTable) {
        $tables[] = $table_mapping->getMongodbDedicatedTableName($definition, $table_mapping->translationsTable);
      }
      if ($table_mapping->allRevisionsTable) {
        $tables[] = $table_mapping->getMongodbDedicatedTableName($definition, $table_mapping->allRevisionsTable);
      }
      if ($table_mapping->latestRevisionTable) {
        $tables[] = $table_mapping->getMongodbDedicatedTableName($definition, $table_mapping->latestRevisionTable);
      }
      if (!$definition->isTranslatable() && !$definition->isRevisionable()) {
        $tables[] = $table_mapping->getMongodbDedicatedTableName($definition, $table_mapping->baseTable);
      }

      foreach ($tables as $table_name) {
        $table_mapping->setFieldNames($table_name, [$field_name]);
        $table_mapping->setExtraColumns($table_name, $extra_columns);
      }
    }

    return $table_mapping;
  }

  /**
   * Gets the all revisions table name.
   *
   * @return string|null
   *   The all revisions table name.
   *
   * @internal
   */
  public function getAllRevisionsTable() {
    return $this->allRevisionsTable;
  }

  /**
   * Gets the current revision table name.
   *
   * @return string|null
   *   The current revision table name.
   *
   * @internal
   */
  public function getCurrentRevisionTable() {
    return $this->currentRevisionTable;
  }

  /**
   * Gets the latest revision table name.
   *
   * @return string|null
   *   The latest revision table name.
   *
   * @internal
   */
  public function getLatestRevisionTable() {
    return $this->latestRevisionTable;
  }

  /**
   * Gets the translations table name.
   *
   * @return string|null
   *   The translations table name.
   *
   * @internal
   */
  public function getTranslationsTable() {
    return $this->translationsTable;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldTableName($field_name) {
    $result = NULL;

    if (isset($this->fieldStorageDefinitions[$field_name])) {
      $storage_definition = $this->fieldStorageDefinitions[$field_name];
      $table_names = [
        $this->baseTable,
      ];

      if (!$storage_definition->isTranslatable() && !$storage_definition->isRevisionable()) {
        $table_names[] = $this->getMongodbDedicatedTableName($storage_definition, $this->baseTable);
      }

      if ($this->translationsTable) {
        $table_names = array_merge($table_names, [
          $this->translationsTable,
          $this->getMongodbDedicatedTableName($storage_definition, $this->translationsTable),
        ]);
      }

      if ($this->currentRevisionTable) {
        $table_names = array_merge($table_names, [
          $this->currentRevisionTable,
          $this->getMongodbDedicatedTableName($storage_definition, $this->currentRevisionTable),
        ]);
      }

      if ($this->latestRevisionTable) {
        $table_names = array_merge($table_names, [
          $this->latestRevisionTable,
          $this->getMongodbDedicatedTableName($storage_definition, $this->latestRevisionTable),
        ]);
      }

      if ($this->allRevisionsTable) {
        $table_names = array_merge($table_names, [
          $this->allRevisionsTable,
          $this->getMongodbDedicatedTableName($storage_definition, $this->allRevisionsTable),
        ]);
      }

      // Collect field columns.
      $field_columns = [];
      foreach (array_keys($storage_definition->getColumns()) as $property_name) {
        $field_columns[] = $this->getFieldColumnName($storage_definition, $property_name);
      }

      foreach (array_filter($table_names) as $table_name) {
        $columns = $this->getAllColumns($table_name);
        // We assume finding one field column belonging to the mapping is enough
        // to identify the field table.
        if (array_intersect($columns, $field_columns)) {
          $result = $table_name;
          break;
        }
      }
    }

    if (!isset($result)) {
      throw new SqlContentEntityStorageException("Table information not available for the '$field_name' field.");
    }

    // The class Drupal\views\ViewsConfigUpdater needs the entity base table
    // name instead of the embedded table name.
    // @todo Need to test if this does not makes the driver slow.
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT & DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    if (isset($backtrace[1]['class']) && ($backtrace[1]['class'] == ViewsConfigUpdater::class)) {
      return $this->entityType->getBaseTable();
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getDedicatedTableNames() {
    $table_mapping = $this;
    $definitions = array_filter($this->fieldStorageDefinitions, function ($definition) use ($table_mapping) {
      return $table_mapping->requiresDedicatedTableStorage($definition);
    });

    $dedicated_all_revisions_tables = [];
    if ($table_mapping->allRevisionsTable) {
      $dedicated_all_revisions_tables = array_map(function ($definition) use ($table_mapping) {
        return $table_mapping->getMongodbDedicatedTableName($definition, $table_mapping->allRevisionsTable);
      }, $definitions);
    }

    $dedicated_current_revision_tables = [];
    if ($table_mapping->currentRevisionTable) {
      $dedicated_current_revision_tables = array_map(function ($definition) use ($table_mapping) {
        return $table_mapping->getMongodbDedicatedTableName($definition, $table_mapping->currentRevisionTable);
      }, $definitions);
    }

    $dedicated_latest_revision_tables = [];
    if ($table_mapping->latestRevisionTable) {
      $dedicated_latest_revision_tables = array_map(function ($definition) use ($table_mapping) {
        return $table_mapping->getMongodbDedicatedTableName($definition, $table_mapping->latestRevisionTable);
      }, $definitions);
    }

    $dedicated_translations_tables = [];
    if ($table_mapping->translationsTable) {
      $dedicated_translations_tables = array_map(function ($definition) use ($table_mapping) {
        return $table_mapping->getMongodbDedicatedTableName($definition, $table_mapping->translationsTable);
      }, $definitions);
    }

    $dedicated_non_revision_non_translation_tables = array_map(function ($definition) use ($table_mapping) {
      if (!$definition->isTranslatable() && !$definition->isRevisionable()) {
        return $table_mapping->getMongodbDedicatedTableName($definition, $table_mapping->baseTable);
      }
    }, $definitions);

    return array_merge(
      array_values($dedicated_all_revisions_tables),
      array_values($dedicated_current_revision_tables),
      array_values($dedicated_latest_revision_tables),
      array_values($dedicated_translations_tables),
      array_values($dedicated_non_revision_non_translation_tables),
    );
  }

  /**
   * Generates a table name for a field embedded table.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field storage definition.
   * @param string $parent_table_name
   *   The parent table name.
   * @param bool $is_deleted
   *   (optional) Whether the table name holding the values of a deleted field
   *   should be returned.
   *
   * @return string
   *   A string containing the generated name for the database table.
   */
  public function getMongodbDedicatedTableName(FieldStorageDefinitionInterface $storage_definition, $parent_table_name, $is_deleted = FALSE) {
    if ($is_deleted) {
      // When a field is a deleted, the table is renamed to
      // {field_deleted_data_FIELD_UUID}. To make sure we don't end up with
      // table names longer than 64 characters, we hash the unique storage
      // identifier and return the first 10 characters so we end up with a short
      // unique ID.
      return "field_deleted_data_" . substr(hash('sha256', $storage_definition->getUniqueStorageIdentifier()), 0, 10);
    }
    else {
      $table_name = $parent_table_name . '__' . $storage_definition->getName();
      // Limit the string to 48 characters, keeping a 16 characters margin for
      // db prefixes.
      if (strlen($table_name) > 48) {
        // Truncate the parent table name and hash the of the field UUID.
        $table_name = substr($parent_table_name, 0, 34) . '__' . substr(hash('sha256', $storage_definition->getUniqueStorageIdentifier()), 0, 10);
      }
      return $table_name;
    }
  }

}
