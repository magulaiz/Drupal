<?php

declare(strict_types=1);

namespace Drupal\Core\Database\SchemaDefinition;

/**
 * Describes a database foreign key.
 */
final class ForeignKey extends KeyBase {

  public readonly array $foreignColumns;

  /**
   * Constructor.
   */
  public function __construct(
    public readonly string $name,
    public readonly string $foreignTable,
    array $columns,
    array $foreignColumns,
  ) {
    parent::__construct($columns);
    $this->foreignColumns = $this->buildColumns($foreignColumns);
    assert(count($this->columns) === count($this->foreignColumns), "Mismatching count of columns for the {$this->name} foreign key.");
  }

}
