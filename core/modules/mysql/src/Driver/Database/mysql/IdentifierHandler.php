<?php

declare(strict_types=1);

namespace Drupal\mysql\Driver\Database\mysql;

use Drupal\Core\Database\Exception\IdentifierException;
use Drupal\Core\Database\Identifier\IdentifierHandlerBase;
use Drupal\Core\Database\Identifier\IdentifierType;

/**
 * MySQL implementation of the identifier handler.
 */
class IdentifierHandler extends IdentifierHandlerBase {

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

  /**
   * {@inheritdoc}
   */
  protected function validateCanonicalName(string $identifier, string $canonicalName, IdentifierType $type): true {
    return match ($type) {
      IdentifierType::Table => true,
      default => parent::validateCanonicalName($identifier, $canonicalName, $type),
    };
  }

  /**
   * {@inheritdoc}
   */
  public function parseTableIdentifier(string $identifier): array {
    $parts = parent::parseTableIdentifier($identifier);
    if ($parts['database']) {
      throw new IdentifierException(sprintf(
        'MySql does not support the syntax [database.][schema.]table for the table identifier \'%s\'. Avoid specifying the \'database\' part',
        $identifier,
      ));
    }
    return $parts;
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveTableForMachine(string $canonicalName, array $info): string {
    if (strlen($info['needs_prefix'] ? $this->tablePrefix : '' . $canonicalName) > $this->getMaxLength(IdentifierType::Table)) {
      $hash = substr(hash('sha256', $canonicalName), 0, 10);
      $shortened = substr($canonicalName, 0, $this->getMaxLength(IdentifierType::Table) - strlen($this->tablePrefix) - 10);
      return $this->quote($info['needs_prefix'] ? $this->tablePrefix . $shortened . $hash : $shortened . $hash);
    }
    return $this->quote($info['needs_prefix'] ? $this->tablePrefix . $canonicalName : $canonicalName);
  }

}
