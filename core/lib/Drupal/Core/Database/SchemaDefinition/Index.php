<?php

declare(strict_types=1);

namespace Drupal\Core\Database\SchemaDefinition;

/**
 * Describes a database index.
 */
final class Index extends KeyBase {

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
