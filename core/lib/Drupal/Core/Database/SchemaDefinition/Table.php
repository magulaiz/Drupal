<?php

declare(strict_types=1);

namespace Drupal\Core\Database\SchemaDefinition;

/**
 * Describes a database table.
 */
final class Table implements SchemaDefinitionInterface {

  /**
   * Constructor.
   *
   * @param string $name
   *   The table name.
   * @param Column[] $columns
   *   An array that describes the table's database columns.
   * @param Property|string $description
   *   (Optional) A string in non-markup plain text describing this table and
   *   its purpose. References to other tables should be enclosed in curly
   *   brackets.
   * @param Property|PrimaryKey $primaryKey
   *   (Optional) The primary key of the table.
   * @param Property|UniqueKey[] $uniqueKeys
   *   (Optional) An array of unique keys for the table.
   * @param Property|Index[] $indexes
   *   (Optional) An array of indexes for the table.
   * @param Property|ForeignKey[] $foreignKeys
   *   (Optional) An array of foreign keys for the table. This argument is for
   *   documentation purposes only; foreign keys are not created in the
   *   database, nor are they enforced by Drupal.
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
