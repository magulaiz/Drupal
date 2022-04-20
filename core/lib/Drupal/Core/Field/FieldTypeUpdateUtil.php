<?php

namespace Drupal\Core\Field;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Utilities for updating field type definitions.
 *
 * Based on https://www.drupal.org/project/drupal/issues/937442#comment-12586376
 */
class FieldTypeUpdateUtil {

  /**
   * Add a new column for fieldType.
   *
   * @param string $field_type
   *   The ID of the field type definition.
   * @param string $property
   *   The name of the property whose column to add.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Database\SchemaObjectDoesNotExistException
   * @throws \Drupal\Core\Database\SchemaObjectExistsException
   */
  public static function addProperty($field_type, $property) {

    $manager = Drupal::entityDefinitionUpdateManager();
    $field_map = Drupal::service('entity_field.manager')
      ->getFieldMapByFieldType($field_type);

    foreach ($field_map as $entity_type_id => $fields) {

      foreach (array_keys($fields) as $field_name) {
        $field_storage_definition = $manager->getFieldStorageDefinition($field_name, $entity_type_id);
        $storage = Drupal::entityTypeManager()->getStorage($entity_type_id);

        if ($storage instanceof SqlContentEntityStorage) {
          $table_mapping = $storage->getTableMapping([
            $field_name => $field_storage_definition,
          ]);
          $table_names = $table_mapping->getDedicatedTableNames();
          $columns = $table_mapping->getColumnNames($field_name);

          foreach ($table_names as $table_name) {
            $field_schema = $field_storage_definition->getSchema();
            $schema = Drupal::database()->schema();
            $field_exists = $schema->fieldExists($table_name, $columns[$property]);
            $table_exists = $schema->tableExists($table_name);

            if (!$field_exists && $table_exists) {
              $schema->addField($table_name, $columns[$property], $field_schema['columns'][$property]);
            }
          }
        }
        $manager->updateFieldStorageDefinition($field_storage_definition);
      }
    }

  }

  /**
   * Update a field schema to match the latest definition,
   * while preserving its data.
   *
   * Updates the whole property schema to match the latest definition
   * from the fields schema().
   * Note: The property schema change has to be compatible
   * with the existing data.
   *
   * @param string $field_type
   *   The ID of the field type definition.
   */
  public static function updateToCurrentSchema($field_type) {
    // @todo: Implement:
    // 1. Get all entity types using this $field_type.
    // 2. Optionally: Ensure the existing data is generally compatible with the new schema.
    // 3. Copy the existing data (as the tables have to be recreated).
    // 4. Update the field storage definition to current for all field instances
    // 5. Restore (insert) the existing data
  }

 /**
   * Updates a property schema definition preserving its data.
   *
   * Use this, if you need to make a change to an existing
   * fields property schema, preserving its values.
   * Note: The property schema change has to be compatible
   * with the existing data and supported by database engines,
   * e.g. change "description", ""not null", "unsigned" or "default".
   * This can't do magic like changing incompatible types!
   *
   * @param string $field_type
   *   The ID of the field type definition.
   * @param string $property
   *   The name of the property whose property schema to update.
   * @param array $new_property_schema
   *   The new schema definition of the single property.
   */
  public static function updatePropertySchema($field_type, $property, array $new_property_schema) {
    // @todo: Implement:
    // 1. Get all entity types using this $field_type.
    // 2. Optionally: Ensure the existing data is generally compatible with the new schema.
    // 3. Copy the existing data (as the tables have to be recreated).
    // 4. Update the field storage definition to current for all field instances
    // 5. Restore (insert) the existing data
  }

  /**
   * Remove a property and column from field_type.
   *
   * @param string $field_type
   *   The ID of the field type definition.
   * @param string $property
   *   The name of the property whose column to remove.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\Sql\SqlContentEntityStorageException
   */
  public static function removeProperty($field_type, $property) {
    $field_map = Drupal::service('entity_field.manager')
      ->getFieldMapByFieldType($field_type);
    foreach ($field_map as $entity_type_id => $fields) {
      foreach (array_keys($fields) as $field_name) {
        self::removePropertyFromEntityType($entity_type_id, $field_name, $property);
      }
    }

  }

  /**
   * Inner function, called by removeProperty.
   *
   * @param string $entity_type_id
   *   The ID of the entity type.
   * @param string $field_name
   *   The ID of the field type definition.
   * @param string $property
   *   The name of the property whose column to remove.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Core\Entity\Sql\SqlContentEntityStorageException
   */
  private static function removePropertyFromEntityType($entity_type_id, $field_name, $property) {
    $entity_type_manager = Drupal::entityTypeManager();
    $entity_update_manager = Drupal::entityDefinitionUpdateManager();
    $entity_storage_schema_sql = Drupal::keyValue('entity.storage_schema.sql');

    $entity_type = $entity_type_manager->getDefinition($entity_type_id);
    $field_storage_definition = $entity_update_manager->getFieldStorageDefinition($field_name, $entity_type_id);
    $entity_storage = Drupal::entityTypeManager()->getStorage($entity_type_id);
    /** @var \Drupal\Core\Entity\Sql\DefaultTableMapping $table_mapping */
    $table_mapping = $entity_storage->getTableMapping([
      $field_name => $field_storage_definition,
    ]);

    // Load the installed field schema so that it can be updated.
    $schema_key = "$entity_type_id.field_schema_data.$field_name";
    $field_schema_data = $entity_storage_schema_sql->get($schema_key);

    // Get table name and revision table name, getFieldTableName NOT WORK,
    // so use getDedicatedDataTableName.
    $table = $table_mapping->getDedicatedDataTableName($field_storage_definition);
    // try/catch.
    $revision_table = NULL;
    if ($entity_type->isRevisionable() && $field_storage_definition->isRevisionable()) {
      if ($table_mapping->requiresDedicatedTableStorage($field_storage_definition)) {
        $revision_table = $table_mapping->getDedicatedRevisionTableName($field_storage_definition);
      }
      elseif ($table_mapping->allowsSharedTableStorage($field_storage_definition)) {
        $revision_table = $entity_type->getRevisionDataTable() ?: $entity_type->getRevisionTable();
      }
    }

    // Save changes to the installed field schema.
    if ($field_schema_data) {
      $_column = $table_mapping->getFieldColumnName($field_storage_definition, $property);
      // Update schema definition in database.
      unset($field_schema_data[$table]['fields'][$_column]);
      if ($revision_table) {
        unset($field_schema_data[$revision_table]['fields'][$_column]);
      }
      $entity_storage_schema_sql->set($schema_key, $field_schema_data);
      // Try to drop field data.
      Drupal::database()->schema()->dropField($table, $_column);
    }
  }

}
