<?php

namespace Drupal\pgsql\Driver\Database\pgsql;

use Drupal\Core\Database\Identifier\IdentifierProcessorBase;
use Drupal\Core\Database\Identifier\IdentifierType;

/**
 * MySQL implementation of the identifier processor.
 */
class IdentifierProcessor extends IdentifierProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function getMaxLength(IdentifierType $type): int {
    // @see https://www.postgresql.org/docs/current/limits.html
    return 63;
  }

}
