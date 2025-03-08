<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Identifier;

/**
 * Enum for database identifier types.
 */
enum IdentifierType: string {
  case Generic = 'unknown';
  case Database = 'database';
  case Schema = 'schema';
  case Sequence = 'sequence';
  case Table = 'table';
  case Column = 'column';
  case Index = 'index';

  case Alias = 'alias';
}
