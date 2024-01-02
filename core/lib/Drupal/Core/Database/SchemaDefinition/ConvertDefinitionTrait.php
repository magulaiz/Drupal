<?php

declare(strict_types=1);

namespace Drupal\Core\Database\SchemaDefinition;

/**
 * A helper trait to convert SchemaDefinition objects to legacy array.
 *
 * @internal
 */
trait ConvertDefinitionTrait {

  /**
   * Converts a Table object.
   *
   * @internal
   */
  final protected static function convertTableToArrayDefinition(Table $table): array {
    $spec = [];
    if ($table->description !== Property::Undefined) {
      $spec['description'] = $table->description;
    }
    $spec['fields'] = self::convertColumnsToArrayDefinition($table->columns);
    if ($table->primaryKey !== Property::Undefined) {
      $spec['primary key'] = self::convertPrimaryKeyToArrayDefinition($table->primaryKey);
    }
    if ($table->uniqueKeys !== Property::Undefined) {
      $spec['unique keys'] = self::convertUniqueKeysToArrayDefinition($table->uniqueKeys);
    }
    if ($table->indexes !== Property::Undefined) {
      $spec['indexes'] = self::convertIndexesToArrayDefinition($table->indexes);
    }
    if ($table->foreignKeys !== Property::Undefined) {
      $spec['foreign keys'] = self::convertForeignKeysToArrayDefinition($table->foreignKeys);
    }
    return $spec;
  }

  /**
   * Converts an array of Column objects.
   *
   * @internal
   */
  final protected static function convertColumnsToArrayDefinition(array $columns): array {
    $spec = [];
    foreach ($columns as $column) {
      $spec[$column->name] = self::convertColumnToArrayDefinition($column);
    }
    return $spec;
  }

  /**
   * Converts a Column object.
   *
   * @internal
   */
  final protected static function convertColumnToArrayDefinition(Column $column): array {
    $spec = [];
    if ($column->type !== Property::Undefined) {
      $spec['type'] = $column->type;
    }
    if ($column->description !== Property::Undefined) {
      $spec['description'] = $column->description;
    }
    if ($column->serialize !== Property::Undefined) {
      $spec['serialize'] = $column->serialize;
    }
    if ($column->size !== Property::Undefined) {
      $spec['size'] = $column->size;
    }
    if ($column->notNull !== Property::Undefined) {
      $spec['not null'] = $column->notNull;
    }
    if ($column->default !== Property::Undefined) {
      $spec['default'] = $column->default;
    }
    if ($column->length !== Property::Undefined) {
      $spec['length'] = $column->length;
    }
    if ($column->unsigned !== Property::Undefined) {
      $spec['unsigned'] = $column->unsigned;
    }
    if ($column->precision !== Property::Undefined) {
      $spec['precision'] = $column->precision;
    }
    if ($column->scale !== Property::Undefined) {
      $spec['scale'] = $column->scale;
    }
    if ($column->binary !== Property::Undefined) {
      $spec['binary'] = $column->binary;
    }
    if ($column->dbSpecificType !== Property::Undefined) {
      foreach ($column->dbSpecificType as $db => $dbType) {
        $spec[$db . '_type'] = $dbType;
      }
    }
    return $spec;
  }

  /**
   * Converts an array of KeyColumn objects.
   *
   * @internal
   */
  final protected static function convertKeyColumnsToArrayDefinition(array $columns): array {
    $spec = [];
    foreach ($columns as $column) {
      $spec[] = self::convertKeyColumnToArrayDefinition($column);
    }
    return $spec;
  }

  /**
   * Converts a KeyColumn object.
   *
   * @internal
   */
  final protected static function convertKeyColumnToArrayDefinition(KeyColumn $column): string|array {
    if ($column->length !== NULL) {
      return [$column->name, $column->length];
    }
    return $column->name;
  }

  /**
   * Converts a PrimaryKey object.
   *
   * @internal
   */
  final protected static function convertPrimaryKeyToArrayDefinition(PrimaryKey $primaryKey): array {
    return self::convertKeyColumnsToArrayDefinition($primaryKey->columns);
  }

  /**
   * Converts an array of UniqueKey objects.
   *
   * @internal
   */
  final protected static function convertUniqueKeysToArrayDefinition(array $uniqueKeys): array {
    $spec = [];
    foreach ($uniqueKeys as $uniqueKey) {
      $spec[$uniqueKey->name] = self::convertKeyColumnsToArrayDefinition($uniqueKey->columns);
    }
    return $spec;
  }

  /**
   * Converts an array of Index objects.
   *
   * @internal
   */
  final protected static function convertIndexesToArrayDefinition(array $indexes): array {
    $spec = [];
    foreach ($indexes as $index) {
      $spec[$index->name] = self::convertKeyColumnsToArrayDefinition($index->columns);
    }
    return $spec;
  }

  /**
   * Converts an array of ForeignKey objects.
   *
   * @internal
   */
  final protected static function convertForeignKeysToArrayDefinition(array $foreignKeys): array {
    $spec = [];
    foreach ($foreignKeys as $foreignKey) {
      $cols = self::convertKeyColumnsToArrayDefinition($foreignKey->columns);
      $foreignCols = self::convertKeyColumnsToArrayDefinition($foreignKey->foreignColumns);
      $match = [];
      for ($i = 0; $i < count($cols); $i++) {
        $match[$cols[$i]] = $foreignCols[$i];
      }
      $spec[$foreignKey->name] = [
        'table' => $foreignKey->foreignTable,
        'columns' => $match,
      ];
    }
    return $spec;
  }

}
