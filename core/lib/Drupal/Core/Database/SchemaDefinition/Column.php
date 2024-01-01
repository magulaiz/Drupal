<?php

declare(strict_types=1);

namespace Drupal\Core\Database\SchemaDefinition;

/**
 * Describes a database table's column.
 */
final class Column implements SchemaDefinitionInterface {

  /**
   * Constructor.
   */
  public function __construct(
    public readonly string $name,
    public readonly string $type,
    public readonly Property|string $description = Property::Undefined,
    public readonly Property|bool $serialize = Property::Undefined,
    public readonly Property|string $size = Property::Undefined,
    public readonly Property|bool $notNull = Property::Undefined,
    public readonly Property|string|int $default = Property::Undefined,
    public readonly Property|int $length = Property::Undefined,
    public readonly Property|bool $unsigned = Property::Undefined,
    public readonly Property|int $precision = Property::Undefined,
    public readonly Property|int $scale = Property::Undefined,
    public readonly Property|bool $binary = Property::Undefined,
    public readonly Property|array $dbSpecificType = Property::Undefined,
  ) {
  }

}
