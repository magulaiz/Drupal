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
   *
   * @param KeyColumn[] $columns
   *   An array of one or more key column specifiers.
   */
  public function __construct(array $columns) {
    $this->columns = $this->buildColumns($columns);
  }

  /**
   * Builds an array of KeyColumn objects from a mixed list of columns.
   *
   * @param list<KeyColumn|string|array{0:string, 1:int}> $rawColumns
   *   The list can be of a mix of KeyColumn objects, strings representing
   *   column names, or arrays to represent limited length keys.
   *
   * @return KeyColumn[]
   *   The normalized list of KeyColumn objects.
   */
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
