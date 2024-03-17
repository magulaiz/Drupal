<?php

declare(strict_types=1);

namespace Drupal\Core\Database\SchemaDefinition;

/**
 * Interface for objects describing database elements.
 */
trait ConvertDefinitionTrait {

  final protected function convertTableToSchemaDefinition(array $spec): array {
    if (!empty($spec['primary key'])) {
      $spec['primary key'] = $this->convertPrimaryKeyToSchemaDefinition($spec['primary key']);
    }
    if (!empty($spec['indexes'])) {
      $spec['indexes'] = $this->convertIndexesToSchemaDefinition($spec['indexes']);
    }
    return $spec;
  }

  final protected function convertPrimaryKeyToSchemaDefinition(PrimaryKey|array $primaryKey): PrimaryKey {
    return $primaryKey instanceof PrimaryKey ? $primaryKey : new PrimaryKey($primaryKey);
  }

  final protected function convertIndexesToSchemaDefinition(array $indexes): array {
    return array_map(fn(int|string $key, Index|array $value): Index => $this->convertIndexToSchemaDefinition($key, $value), array_keys($indexes), $indexes);
  }

  final protected function convertIndexToSchemaDefinition(string|int $name, Index|array $index): Index {
    return $index instanceof Index ? $index : new Index($name, $index);
  }

}
