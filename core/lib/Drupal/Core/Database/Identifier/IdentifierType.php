<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Identifier;

/**
 * Enumeration of database identifier types.
 */
enum IdentifierType: string {
  case Database = 'database';
  case Schema = 'schema';
  case Sequence = 'sequence';
  case Table = 'table';
  case Column = 'column';
  case Index = 'index';

  case Alias = 'alias';

  case Unknown = 'unknown';
}
