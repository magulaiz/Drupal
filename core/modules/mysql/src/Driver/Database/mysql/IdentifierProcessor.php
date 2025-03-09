<?php

namespace Drupal\mysql\Driver\Database\mysql;

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
    // @see https://dev.mysql.com/doc/refman/8.4/en/identifier-length.html
    return match ($type) {
      IdentifierType::Alias => 256,
      default => 64,
    };
  }

}
