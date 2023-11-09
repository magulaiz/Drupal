<?php

namespace Drupal\Core\Database\Query;

use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

// cSpell:ignore bigserial

/**
 * The condition parameter type check service.
 *
 * @ingroup database
 */
class ConditionParameterTypeCheck {

  /**
   * The key value storage value for entity schema storage.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $entityStorageSchemaSql;

  /**
   * The table schema data.
   *
   * @var array
   */
  protected $schemas = [];

  /**
   * Constructs a ConditionParameterTypeCheck object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $keyValue
   *   The key value factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(
    protected ModuleHandlerInterface $moduleHandler,
    protected KeyValueFactoryInterface $keyValue,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {
    $this->entityStorageSchemaSql = $this->keyValue->get('entity.storage_schema.sql');
  }

  /**
   * Check condition parameters of being of the correct type.
   *
   * Generates a user-level error when for a condition the condition field type
   * is not the same as the condition value type.
   *
   * @param array $table_names
   *   The list of table names that are used in the query.
   * @param array $conditions
   *   The conditions to be parameter type checked.
   */
  public function checkParameterTypes(array $table_names, array $conditions): void {
    $schemas = $this->getSchemaTables($table_names);
    foreach ($conditions as $condition) {
      if (isset($condition['field']) && $condition['field'] instanceof ConditionInterface) {
        // Recursively call this method to check all nested conditions.
        $this->checkParameterTypes($table_names, $condition['field']->conditions());
      }
      elseif (isset($condition['field']) && isset($condition['value'])) {
        if ($matched_field_type = $this->getMatchedFieldType($condition['field'], $schemas)) {
          $throw_error = FALSE;
          $error_message = t('Using a query condition where the condition value is not of the same type as the field type is not allowed.');
          if (isset($condition['operator']) && in_array(strtoupper($condition['operator']), ['IN', 'NOT IN'], TRUE)) {
            foreach ($condition['value'] as $value) {
              if (!in_array(gettype($value), [$matched_field_type, 'null'], TRUE)) {
                $throw_error = TRUE;
                $error_message .= sprintf(' The condition on the field "%s" is of the type "%s" and its condition value is an array of "%s", which contains a value of "%s".',
                  $condition['field'],
                  $matched_field_type,
                  $matched_field_type,
                  gettype($value),
                );
              }
            }
          }
          elseif (!in_array(gettype($condition['value']), [$matched_field_type, 'null'], TRUE)) {
            $throw_error = TRUE;
            $error_message .= sprintf(' The condition on the field "%s" is of the type "%s" and its condition value is of the type "%s".',
              $condition['field'],
              $matched_field_type,
              gettype($condition['value']),
            );
          }

          if ($throw_error) {
            //dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            @trigger_error($error_message, E_USER_DEPRECATED);
          }
        }
      }
    }
  }

  /**
   * Get the type for the matched field.
   *
   * For now: only get the matched fields when they are integer types.
   *
   * @param string $field_name
   *   The field name to match for.
   * @param array $schemas
   *   The table schemas keyed by their table name.
   *
   * @return string|false
   *   The gettype value or FALSE when no or multiple values.
   */
  protected function getMatchedFieldType(string $field_name, array $schemas = []): string|FALSE {
    $matched_fields = [];
    foreach ($schemas as $schema) {
      if (isset($schema['fields']) && is_array($schema['fields'])) {
        if (in_array($field_name, array_keys($schema['fields']), TRUE) && isset($schema['fields'][$field_name]['type'])) {
          $field_type = $schema['fields'][$field_name]['type'];
          if (in_array($field_type, ['int', 'serial', 'bigserial'])) {
            // Use the value as returned by gettype().
            $matched_fields[] = 'integer';
          }
        }
      }
    }

    // Remove double values.
    $matched_fields = array_unique($matched_fields);

    // Only return a matched field type when we have a single result.
    if (count($matched_fields) == 1) {
      return reset($matched_fields);
    }

    return FALSE;
  }

  /**
   * Get table schemas for requested tables.
   *
   * @param array $table_names
   *   An array of table names
   *
   * @return array
   *   The all the table schemas keyed by their table name.
   */
  protected function getSchemaTables(array $table_names = []): array {
    $load_schema = [];
    foreach ($table_names as $table_name) {
      if (!isset($this->schemas[$table_name])) {
        $load_schema[] = $table_name;
      }
    }

    if (!empty($load_schema)) {
      // Load the hook_schema tables only once.
      if (empty($this->schemas)) {
        // Reset the module handler cache to get the right table schemas.
        $this->moduleHandler->resetImplementations();

        // Get the schemas for the tables created in hook_schema().
        $schemas = $this->moduleHandler->invokeAll('schema') ?? [];
        foreach ($schemas as $table_name => $table_schema) {
          $this->schemas[$table_name] = $table_schema;
        }
      }

      $schema_config = $this->entityStorageSchemaSql->getAll();
      foreach ($schema_config as $config_key => $config_value) {
        // Get all non field data.
        if (str_ends_with($config_key, '.entity_schema_data')) {
          foreach ($config_value as $table_name => $table_schema) {
            foreach ($table_schema as $schema_key => $schema_values) {
              $this->schemas[$table_name][$schema_key] = $schema_values;
            }
          }
        }

        // Get the table fields.
        if (str_contains($config_key, '.field_schema_data.')) {
          foreach ($config_value as $table_name => $table_schema) {
            if (isset($table_schema['fields'])) {
              $this->schemas[$table_name]['fields'] = $table_schema['fields'];
            }
          }
        }
      }
    }

    // Filter for only the requested tables.
    return array_filter($this->schemas, function ($table_schema, $table_name) use ($table_names) {
      if (in_array($table_name, $table_names, TRUE)) {
        return $table_schema;
      }
    }, ARRAY_FILTER_USE_BOTH);
  }

  /**
   * Get all active the entity type ids.
   *
   * @return array
   *   The list of active entity type ids.
   */
  protected function getEntityTypeIds(): array {
    $entity_type_ids = [];
    $entity_types = $this->entityTypeManager->getDefinitions();
    foreach ($entity_types as $entity_type) {
      $entity_type_ids[] = $entity_type->id();
    }
    return $entity_type_ids;
  }

}
