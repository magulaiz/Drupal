<?php

declare(strict_types=1);

namespace Drupal\TestTools\Extension;

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
      $tables = $handler->invoke($module, 'schema', [FALSE]) ?? [];
      $temp = [];
      foreach ($tables as $name => $table) {
        if ($table instanceof Table) {
          $name = $table->name;
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
