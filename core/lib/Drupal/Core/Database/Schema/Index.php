<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Schema;

use Drupal\Core\Database\SchemaDefinition\Index as IndexDefinition;

/**
 * @todo
 */
class Index extends SchemaElementBase {

  public string $name;
  public array $columns = [];

  public function getColumnNames(): array {
    return array_map(fn(KeyColumn $column): string => $column->name, $this->columns);
  }

}
