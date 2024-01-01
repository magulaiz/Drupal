<?php

declare(strict_types=1);

namespace Drupal\Core\Database\SchemaDefinition;

/**
 * Describes a database unique key.
 */
final class UniqueKey extends KeyBase {

  /**
   * Constructor.
   */
  public function __construct(
    public readonly string $name,
    array $columns,
  ) {
    parent::__construct($columns);
  }

}
