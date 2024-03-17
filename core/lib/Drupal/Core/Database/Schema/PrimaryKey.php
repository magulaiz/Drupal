<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Schema;

use Drupal\Core\Database\SchemaDefinition\PrimaryKey as PrimaryKeyDefinition;

/**
 * @todo
 */
class PrimaryKey extends SchemaElementBase {

  public array $columns = [];

  public function getColumnNames(): array {
    return array_map(fn(KeyColumn $column): string => $column->name, $this->columns);
  }

}
