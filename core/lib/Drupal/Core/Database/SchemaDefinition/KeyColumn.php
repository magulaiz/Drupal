<?php

declare(strict_types=1);

namespace Drupal\Core\Database\SchemaDefinition;

/**
 * Describes a column of a database index or key.
 */
final class KeyColumn implements SchemaDefinitionInterface {

  /**
   * Constructor.
   */
  public function __construct(
    public readonly string $name,
    public readonly ?int $length = NULL,
  ) {
  }

}
