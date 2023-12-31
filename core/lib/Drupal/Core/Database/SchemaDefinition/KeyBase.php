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
    $cols = [];
    foreach ($columns as $columnDefinition) {
      if ($columnDefinition instanceof KeyColumn) {
        $cols[] = $columnDefinition;
      }
      elseif (is_array($columnDefinition)) {
        $cols[] = new KeyColumn($columnDefinition[0], $columnDefinition[1]);
      }
      else {
        $cols[] = new KeyColumn($columnDefinition);
      }
    }
    $this->columns = $cols;
  }

}
