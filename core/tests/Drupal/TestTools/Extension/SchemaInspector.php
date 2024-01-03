<?php

namespace Drupal\TestTools\Extension;

use Drupal\Core\Database\Exception\SchemaDefinitionException;
use Drupal\Core\Database\SchemaDefinition\ConvertDefinition;
use Drupal\Core\Database\SchemaDefinition\Table;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides methods to access modules' schema.
 */
class SchemaInspector {

  /**
   * Returns the module's schema specification.
   *
   * This function can be used to retrieve a schema specification provided by
   * hook_schema(), so it allows you to derive your tables from existing
   * specifications.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $handler
   *   The module handler to use for calling schema hook.
   * @param string $module
   *   The module to which the table belongs.
   *
   * @return array
   *   An array of schema definition provided by hook_schema().
   *
   * @see \hook_schema()
   */
  public static function getTablesSpecification(ModuleHandlerInterface $handler, string $module): array {
    if ($handler->loadInclude($module, 'install')) {
      $tables = $handler->invoke($module, 'schema') ?? [];
      $temp = [];
      foreach ($tables as $name => $table) {
        if ($table instanceof Table) {
          if (is_int($name)) {
            $name = $table->name;
          }
          if ($name !== $table->name) {
            throw new SchemaDefinitionException("The '{$name}' key returned by the {$module}_schema() function must be equal to the Table::\$name property; found '{$table->name}'");
          }
          if (!\Drupal::database()->supportsSchemaDefinition()) {
            $table = ConvertDefinition::tableToArray($table);
          }
        }
        $temp[$name] = $table;
      }
      return $temp;
    }
    return [];
  }

}
