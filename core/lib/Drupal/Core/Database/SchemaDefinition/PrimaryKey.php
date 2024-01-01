<?php

declare(strict_types=1);

namespace Drupal\Core\Database\SchemaDefinition;

/**
 * Describes a table's primary key.
 */
final class PrimaryKey extends KeyBase {

  /**
   * Constructor.
   */
  public function __construct(array $columns) {
    parent::__construct($columns);
  }

}
