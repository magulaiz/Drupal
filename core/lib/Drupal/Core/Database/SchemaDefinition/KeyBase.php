<?php

declare(strict_types=1);

namespace Drupal\Core\Database\SchemaDefinition;

/**
 * Base class for table keys (primary, unique, index).
 */
abstract class KeyBase implements SchemaDefinitionInterface {

  public readonly array $columns;

  /**
   * Constructor.
   */
  public function __construct(array $columns) {
    $this->columns = $this->buildColumns($columns);
  }

  protected function buildColumns(array $rawColumns): array {
    $columns = [];
    foreach ($rawColumns as $rawColumn) {
      if ($rawColumn instanceof KeyColumn) {
        $columns[] = $rawColumn;
      }
      elseif (is_array($rawColumn)) {
        $columns[] = new KeyColumn($rawColumn[0], $rawColumn[1]);
      }
      else {
        $columns[] = new KeyColumn($rawColumn);
      }
    }
    return $columns;
  }

}
