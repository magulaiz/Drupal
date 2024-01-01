<?php

declare(strict_types=1);

namespace Drupal\Core\Database\SchemaDefinition;

/**
 * Describes a database table.
 */
final class Table implements SchemaDefinitionInterface {

  /**
   * Constructor.
   */
  public function __construct(
    public readonly string $name,
    public readonly array $columns,
    public readonly Property|string $description = Property::Undefined,
    public readonly Property|PrimaryKey $primaryKey = Property::Undefined,
    public readonly Property|array $uniqueKeys = Property::Undefined,
    public readonly Property|array $indexes = Property::Undefined,
    public readonly Property|array $foreignKeys = Property::Undefined,
  ) {
  }

}
