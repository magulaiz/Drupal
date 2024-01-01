<?php

declare(strict_types=1);

namespace Drupal\Core\Database\SchemaDefinition;

/**
 * Describes a table's primary key.
 */
final class PrimaryKey extends KeyBase {

  /**
   * Constructor.
   *
   * @param KeyColumn[] $columns
   *   An array of one or more key column specifiers that form the primary key.
   */
  public function __construct(array $columns) {
    parent::__construct($columns);
  }

}
