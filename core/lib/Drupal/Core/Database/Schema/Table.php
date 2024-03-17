<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Schema;

/**
 * Temporary Table.
 *
 * @todo complete as a SchemaElementBase.
 */
class Table {

  public PrimaryKey $primaryKey;
  public array $indexes = [];

  public function __construct(
    public string $name,
    public array $spec,
  ) {
  }

}
