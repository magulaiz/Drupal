<?php

namespace Drupal\Core\Entity\Query\Sql;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryException;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Entity\Sql\TableMappingInterface;
use Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceDefinitionInterface;

/**
 * Adds tables and fields to the SQL entity query.
 */
class Tables implements TablesInterface {

  /**
   * @var \Drupal\Core\Database\Query\SelectInterface
   */
  protected $sqlQuery;

  /**
   * Entity table array.
   *
   * This array contains at most two entries: one for the data, one for the
   * properties. Its keys are unique references to the tables, values are
   * aliases.
   *
   * @see \Drupal\Core\Entity\Query\Sql\Tables::ensureEntityTable().
   *
   * @var array
   */
  protected $entityTables = [];

  /**
   * Field table array, key is table name, value is alias.
   *
   * This array contains one entry per field table.
   *
   * @var array
   */
  protected $fieldTables = [];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * List of case sensitive fields.
   *
   * @var array
   */
  protected $caseSensitiveFields = [];

  /**
   * @param \Drupal\Core\Database\Query\SelectInterface $sql_query
   *   The SQL query.
   */
  public function __construct(SelectInterface $sql_query) {
    $this->sqlQuery = $sql_query;
    $this->entityTypeManager = \Drupal::entityTypeManager();
    $this->entityFieldManager = \Drupal::service('entity_field.manager');
  }

  /**
   * {@inheritdoc}
   */
  public function addField($field, $join_type, $langcode) {
    $entity_type_id = $this->sqlQuery->getMetaData('entity_type');
    // This variable ensures grouping works correctly. For example:
    // ->condition('tags', 2, '>')
    // ->condition('tags', 20, '<')
    // ->condition('node_reference.nid.entity.tags', 2)
    // The first two should use the same table but the last one needs to be a
    // new table. So for the first two, the table array index will be 'tags'
    // while the third will be 'node_reference.nid.tags'.
    $index_prefix = '';
    $specifiers = explode('.', $field);
    $base_table = 'base_table';
    $entity_type = $this->entityTypeManager->getActiveDefinition($entity_type_id);

    $field_storage_definitions = $this->entityFieldManager->getActiveFieldStorageDefinitions($entity_type_id);
    // This loop consumes all specifiers. Each iteration consumes a
    // fieldname.delta.propertyname condition. However, fieldname alone and
    // fieldname.propertyname are also all valid combinations, it's not
    // possible to tell ahead of the time how many specifiers will be consumed
    // in one iteration so foreach() and similar doesn't work well here.
    while ($specifiers) {
      $delta = NULL;
      $relationship_specifier = FALSE;

      // The first specifier must be a field name.
      $specifier = array_shift($specifiers);
      $field_storage_definition = FALSE;
      if (isset($field_storage_definitions[$specifier])) {
        $field_storage_definition = $field_storage_definitions[$specifier];
      }
      else {
        $bc_parts = explode('__', $specifier);
        if (count($bc_parts) === 2) {
          // cSpell:disable-next-line
          // trigger_error("Entity query on fieldname__propertyname specifiers is deprecated in drupal:9.4.0 and is removed from drupal:10.0.0. Invalid specifier: $specifier. See https://www.drupal.org/project/drupal/issues/3278083", E_USER_DEPRECATED);
          $specifier = $bc_parts[0];
          if (isset($field_storage_definitions[$specifier])) {
            $field_storage_definition = $field_storage_definitions[$specifier];
            array_unshift($specifiers, $bc_parts[1]);
          }
        }
      }
      if (!$field_storage_definition) {
        throw new QueryException("Field $specifier not found");
      }
      // Next, figure out what column we are dealing with.
      /** @var \Drupal\Core\Entity\Sql\DefaultTableMapping $table_mapping */
      $table_mapping = $this->entityTypeManager->getStorage($entity_type_id)->getTableMapping();
      $specifier = array_shift($specifiers);
      // $specifier also can be a numeric delta for example
      // ->condition('field_name.1.property_name') or
      // TableMappingInterface::DELTA for example
      // ->condition('field_name.%delta.property_name').
      // Both of these require different handling for dedicated and shared
      // table storage.
      if (is_numeric($specifier)) {
        if ($table_mapping->requiresDedicatedTableStorage($field_storage_definition)) {
          // This is a delta condition.
          $delta = $specifier;
          $index_prefix .= ".$delta";
        }
        elseif ($specifier > 0) {
          // In a shared table only delta 0 values exist.
          // @TODO this is a bug because this shortcut is only valid when the
          // operator of the condition is =.
          // https://www.drupal.org/project/drupal/issues/3256162
          $this->sqlQuery->alwaysFalse();
        }
        // Always skip to the next specifier.
        $specifier = array_shift($specifiers);
      }
      elseif ($specifier === TableMappingInterface::DELTA) {
        if ($table_mapping->requiresDedicatedTableStorage($field_storage_definition)) {
          // @TODO is this distinction necessary? Should $index_prefix include
          // TableMappingInterface::DELTA for shared tables too?
          // https://www.drupal.org/project/drupal/issues/2971116 is probably
          // related.
          $index_prefix .= TableMappingInterface::DELTA;
        }
        elseif (!$specifiers) {
          // Field values in shared tables always have a delta of 0. Abort
          // further processing of ->condition('field_name.%delta') in this
          // case as there's no point.
          // @todo this probably should be an exception and again it's likely
          // Condition only handles this properly when the operator is =.
          // https://www.drupal.org/project/drupal/issues/3256162
          return 0;
        }

        // Note: for ->condition('field_name.%delta') %delta needs to be mapped, only skip to the next if there are
        // additional specifiers.
        if ($specifiers) {
          $specifier = array_shift($specifiers);
        }
      }
      // For both ->condition('field_name.1.property_name') and
      // ->condition('field_name.%delta.property_name') the SQL column to be
      // used is property_name, so get it.
      // Note: for ->condition('field_name.%delta') %delta needs to be mapped.
      if (is_numeric($specifier) || ($specifier === TableMappingInterface::DELTA && $specifiers)) {
        $specifier = array_shift($specifiers);
      }
      // With the special fields done, we now have either a property name or
      // a relationship specifier (if anything at all).
      $potential_columns = $field_storage_definition->getColumns();
      if ($table_mapping->requiresDedicatedTableStorage($field_storage_definition)) {
        $potential_columns += array_flip($table_mapping->getReservedColumns());
        $potential_columns[TableMappingInterface::DELTA] = TRUE;
      }
      if (isset($potential_columns[$specifier])) {
        $property_name = $specifier;
      }
      else {
        // If the property could not be mapped to a SQL column, it must be a
        // relationship specifier (if anything at all) because the special
        // delta cases are already handled.
        $relationship_specifier = $specifier;
        // Use the main property name in this case.
        $property_name = $field_storage_definition->getMainPropertyName();
      }
      $sql_column = $table_mapping->getFieldColumnName($field_storage_definition, $property_name);
      // We have the SQL column, add the relevant table.
      $table = $this->addTable($field_storage_definition, $entity_type, $join_type, $index_prefix, $langcode, $base_table, $delta, $sql_column);

      $this->collectCaseSensitivity($field_storage_definition, $property_name);

      // Now handle relationships.
      // If the current specifier was a legit field property, the next
      // specifier could be the relationship specifier (if anything at all).
      if (!$relationship_specifier) {
        $relationship_specifier = array_shift($specifiers);
      }
      if ($relationship_specifier) {
        $propertyDefinitions = $field_storage_definition->getPropertyDefinitions();
        $next_index_prefix = $relationship_specifier;
        $entity_type_id = NULL;
        // Relationship specifier can also contain the entity type ID, i.e.
        // entity:node, entity:user or entity:taxonomy.
        if (str_contains($relationship_specifier, ':')) {
          [$relationship_specifier, $entity_type_id] = explode(':', $relationship_specifier, 2);
        }
        // Check for a valid relationship.
        if (isset($propertyDefinitions[$relationship_specifier]) && $propertyDefinitions[$relationship_specifier] instanceof DataReferenceDefinitionInterface) {
          // If it is, use the entity type if specified already, otherwise use
          // the definition.
          $target_definition = $propertyDefinitions[$relationship_specifier]->getTargetDefinition();
          if (!$entity_type_id && $target_definition instanceof EntityDataDefinitionInterface) {
            $entity_type_id = $target_definition->getEntityTypeId();
          }
          $entity_type = $this->entityTypeManager->getActiveDefinition($entity_type_id);
          $field_storage_definitions = $this->entityFieldManager->getActiveFieldStorageDefinitions($entity_type_id);
          // Add the new entity base table using the table and sql column.
          $base_table = $this->addNextBaseTable($entity_type, $table, $sql_column, $field_storage_definition);
          $index_prefix .= "$next_index_prefix.";
        }
        else {
          throw new QueryException("Invalid specifier '$relationship_specifier'");
        }
      }
    }
    return "$table.$sql_column";
  }

  /**
   * {@inheritdoc}
   */
  public function isFieldCaseSensitive($field_name) {
    if (isset($this->caseSensitiveFields[$field_name])) {
      return $this->caseSensitiveFields[$field_name];
    }
  }

  /**
   * Joins the entity table, if necessary, and returns the alias for it.
   *
   * @param string $index_prefix
   *   The table array index prefix. For a base table this will be empty,
   *   for a target entity reference like 'field_tags.entity:taxonomy_term.name'
   *   this will be 'entity:taxonomy_term.target_id.'.
   * @param string $property
   *   The field property/column.
   * @param $join_type
   *   The join type.
   * @param string $langcode
   *   The langcode we use on the join.
   * @param string $base_table
   *   The table to join to. It can be either the table name, its alias or the
   *   'base_table' placeholder.
   * @param string $id_field
   *   The name of the ID field/property for the current entity. For instance:
   *   tid, nid, etc.
   * @param array $entity_tables
   *   Array of entity tables (data and base tables) where decide the entity
   *   property will be queried from. The first table containing the property
   *   will be used, so the order is important and the data table is always
   *   preferred.
   *
   * @return string
   *   The alias of the joined table.
   *
   * @throws \Drupal\Core\Entity\Query\QueryException
   *   When an invalid property has been passed.
   */
  protected function ensureEntityTable($index_prefix, $property, $join_type, $langcode, $base_table, $id_field, $entity_tables) {
    foreach ($entity_tables as $table => $mapping) {
      if (isset($mapping[$property])) {
        // Ensure a table joined multiple times through different index prefixes
        // has unique entityTables entries by concatenating the index prefix
        // and the base table alias. In this way i.e. if we join to the same
        // entity table several times for different entity reference fields,
        // each join gets a separate alias.
        $key = $index_prefix . ($base_table === 'base_table' ? $table : $base_table);
        if (!isset($this->entityTables[$key])) {
          $this->entityTables[$key] = $this->addJoin($join_type, $table, "[%alias].[$id_field] = [$base_table].[$id_field]", $langcode);
        }
        return $this->entityTables[$key];
      }
    }
    throw new QueryException("'$property' not found");
  }

  /**
   * Ensure the field table is joined if necessary.
   *
   * @param string $index_prefix
   *   The table array index prefix. For a base table this will be empty,
   *   for a target entity reference like 'field_tags.entity:taxonomy_term.name'
   *   this will be 'entity:taxonomy_term.target_id.'.
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface &$field
   *   The field storage definition for the field being joined.
   * @param $join_type
   *   The join type.
   * @param string $langcode
   *   The langcode we use on the join.
   * @param string $base_table
   *   The table to join to. It can be either the table name, its alias or the
   *   'base_table' placeholder.
   * @param string $entity_id_field
   *   The name of the ID field/property for the current entity. For instance:
   *   tid, nid, etc.
   * @param string $field_id_field
   *   The column representing the id for the field. For example, 'revision_id'
   *   or 'entity_id'.
   * @param string $delta
   *   A delta which should be used as additional condition.
   *
   * @return string
   *   The alias of the joined table.
   */
  protected function ensureFieldTable($index_prefix, &$field, $join_type, $langcode, $base_table, $entity_id_field, $field_id_field, $delta) {
    $field_name = $field->getName();
    if (!isset($this->fieldTables[$index_prefix . $field_name])) {
      $entity_type_id = $this->sqlQuery->getMetaData('entity_type');
      /** @var \Drupal\Core\Entity\Sql\DefaultTableMapping $table_mapping */
      $table_mapping = $this->entityTypeManager->getStorage($entity_type_id)->getTableMapping();
      $table = !$this->sqlQuery->getMetaData('all_revisions') ? $table_mapping->getDedicatedDataTableName($field) : $table_mapping->getDedicatedRevisionTableName($field);
      if ($field->getCardinality() != 1) {
        $this->sqlQuery->addMetaData('simple_query', FALSE);
      }
      $this->fieldTables[$index_prefix . $field_name] = $this->addJoin($join_type, $table, "[%alias].[$field_id_field] = [$base_table].[$entity_id_field]", $langcode, $delta);
    }
    return $this->fieldTables[$index_prefix . $field_name];
  }

  /**
   * Adds a join to a given table.
   *
   * @param string $type
   *   The join type.
   * @param string $table
   *   The table to join to.
   * @param string $join_condition
   *   The condition on which to join to.
   * @param string $langcode
   *   The langcode we use on the join.
   * @param string|null $delta
   *   (optional) A delta which should be used as additional condition.
   *
   * @return string
   *   Returns the alias of the joined table.
   */
  protected function addJoin($type, $table, $join_condition, $langcode, $delta = NULL) {
    $arguments = [];
    if ($langcode) {
      $entity_type_id = $this->sqlQuery->getMetaData('entity_type');
      $entity_type = $this->entityTypeManager->getActiveDefinition($entity_type_id);
      // Only the data table follows the entity language key, dedicated field
      // tables have a hard-coded 'langcode' column.
      $langcode_key = $entity_type->getDataTable() == $table ? $entity_type->getKey('langcode') : 'langcode';
      $placeholder = ':langcode' . $this->sqlQuery->nextPlaceholder();
      $join_condition .= ' AND [%alias].[' . $langcode_key . '] = ' . $placeholder;
      $arguments[$placeholder] = $langcode;
    }
    if (isset($delta)) {
      $placeholder = ':delta' . $this->sqlQuery->nextPlaceholder();
      $join_condition .= ' AND [%alias].[delta] = ' . $placeholder;
      $arguments[$placeholder] = $delta;
    }
    return $this->sqlQuery->addJoin($type, $table, NULL, $join_condition, $arguments);
  }

  /**
   * Gets the schema for the given table.
   *
   * @param string $table
   *   The table name.
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return array|false
   *   An associative array of table field mapping for the given table, keyed by
   *   columns name and values are just incrementing integers. If the table
   *   mapping is not available, FALSE is returned.
   */
  protected function getTableMapping($table, $entity_type_id) {
    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    if ($storage instanceof SqlEntityStorageInterface) {
      $mapping = $storage->getTableMapping()->getAllColumns($table);
    }
    else {
      return FALSE;
    }
    return array_flip($mapping);
  }

  /**
   * Add the next entity base table.
   *
   * For example, when building the SQL query for
   * @code
   * condition('uid.entity.name', 'foo', 'CONTAINS')
   * @endcode
   *
   * this adds the users table.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type being joined, in the above example, User.
   * @param string $table
   *   This is the table being joined, in the above example, {users}.
   * @param string $sql_column
   *   This is the SQL column in the existing table being joined to.
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $field_storage
   *   The field storage definition for the field referencing this column.
   *
   * @return string
   *   The alias of the next entity table joined in.
   */
  protected function addNextBaseTable(EntityTypeInterface $entity_type, $table, $sql_column, FieldStorageDefinitionInterface $field_storage) {
    $join_condition = '[%alias].[' . $entity_type->getKey('id') . "] = [$table].[$sql_column]";
    return $this->sqlQuery->leftJoin($entity_type->getBaseTable(), NULL, $join_condition);
  }

  /**
   * Adds the table for a given field.
   *
   * @param $field_storage_definition
   *   The field storage definition.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @param $join_type
   *   The join type.
   * @param string $index_prefix
   *   The table array index prefix. For a base table this will be empty,
   *   for a target entity reference like 'field_tags.entity:taxonomy_term.name'
   *   this will be 'entity:taxonomy_term.target_id.'.
   * @param string $langcode
   *   The langcode we use on the join.
   * @param string $base_table
   *   The table to join to. It can be either the table name, its alias or the
   *   'base_table' placeholder.
   * @param $delta
   *   A delta which should be used as additional condition.
   * @param string $sql_column
   *   The SQL column in the existing table being joined to.
   *
   * @return string
   *   The alias of the table added.
   */
  protected function addTable($field_storage_definition, EntityTypeInterface $entity_type, $join_type, $index_prefix, $langcode, $base_table, $delta, $sql_column) {
    $entity_type_id = $entity_type->id();
    $all_revisions = $this->sqlQuery->getMetaData('all_revisions');
    /** @var \Drupal\Core\Entity\Sql\DefaultTableMapping $table_mapping */
    $table_mapping = $this->entityTypeManager->getStorage($entity_type_id)->getTableMapping();
    // If there is revision support, all the revisions are being queried, and
    // the field is revisionable or the revision ID field itself, then use the
    // revision ID. Otherwise, the entity ID will do.
    $query_revisions = $all_revisions && ($field_storage_definition->isRevisionable() || $field_storage_definition->getName() === $entity_type->getKey('revision'));
    if ($query_revisions) {
      // This contains the relevant SQL field to be used when joining entity
      // tables.
      $entity_id_field = $entity_type->getKey('revision');
      // This contains the relevant SQL field to be used when joining field
      // tables.
      $field_id_field = 'revision_id';
    }
    else {
      $entity_id_field = $entity_type->getKey('id');
      $field_id_field = 'entity_id';
    }

    if ($table_mapping->requiresDedicatedTableStorage($field_storage_definition)) {
      $table = $this->ensureFieldTable($index_prefix, $field_storage_definition, $join_type, $langcode, $base_table, $entity_id_field, $field_id_field, $delta);
    }
    // The field is stored in a shared table.
    else {
      // ensureEntityTable() decides whether an entity property will be
      // queried from the data table or the base table based on where it
      // finds the property first. The data table is preferred, which is why
      // it gets added before the base table.
      $entity_tables = [];
      $revision_table = NULL;
      if ($query_revisions) {
        $data_table = $entity_type->getRevisionDataTable();
        $entity_base_table = $entity_type->getRevisionTable();
      }
      else {
        $data_table = $entity_type->getDataTable();
        $entity_base_table = $entity_type->getBaseTable();

        if ($field_storage_definition && $field_storage_definition->isRevisionable() && in_array($field_storage_definition->getName(), $entity_type->getRevisionMetadataKeys())) {
          $revision_table = $entity_type->getRevisionTable();
        }
      }
      if ($data_table) {
        $this->sqlQuery->addMetaData('simple_query', FALSE);
        $entity_tables[$data_table] = $this->getTableMapping($data_table, $entity_type_id);
      }
      if ($revision_table) {
        $entity_tables[$revision_table] = $this->getTableMapping($revision_table, $entity_type_id);
      }
      $entity_tables[$entity_base_table] = $this->getTableMapping($entity_base_table, $entity_type_id);
      $table = $this->ensureEntityTable($index_prefix, $sql_column, $join_type, $langcode, $base_table, $entity_id_field, $entity_tables);
    }
    return $table;
  }

  /**
   * @param $field_storage_definition
   *   The field storage definition.
   * @param $property_name
   *   The field property name.
   */
  public function collectCaseSensitivity(FieldStorageDefinitionInterface $field_storage_definition, $property_name) {
    $property_definitions = $field_storage_definition->getPropertyDefinitions();
    if (isset($property_definitions[$property_name])) {
      $this->caseSensitiveFields[$field_storage_definition->getName()] = $property_definitions[$property_name]->getSetting('case_sensitive');
    }
  }

}
