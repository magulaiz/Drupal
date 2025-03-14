<?php

declare(strict_types=1);

namespace Drupal\pgsql\Driver\Database\pgsql;

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
    // @see https://www.postgresql.org/docs/current/limits.html
    return 63;
  }

  /**
   * {@inheritdoc}
   */
  protected function validateCanonicalName(string $identifier, string $canonicalName, IdentifierType $type): true {
    return match ($type) {
      IdentifierType::Table => TRUE,
      default => parent::validateCanonicalName($identifier, $canonicalName, $type),
    };
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveTableForMachine(string $canonicalName, array $info): string {
    if ($info['schema']) {
      // We are processing a fully qualified table identifier, canonical name
      // should not be processed, just checked it does not exceed max length.
      if (strlen($canonicalName) > $this->getMaxLength(IdentifierType::Table)) {
        throw new IdentifierException(sprintf(
          'Table identifier \'%s\' exceeds maximum allowed length (%d)',
          $canonicalName,
          $this->getMaxLength(IdentifierType::Table),
        ));
      }
      return $this->quote($canonicalName);
    }

    $prefix = $info['needs_prefix'] ? $this->tablePrefix : '';
    if (strlen($prefix . $canonicalName) > $this->getMaxLength(IdentifierType::Table)) {
      $hash = substr(hash('sha256', $canonicalName), 0, 10);
      $allowedLength = $this->getMaxLength(IdentifierType::Table) - strlen($this->tablePrefix) - 10;
      if ($allowedLength < 4) {
        throw new IdentifierException(sprintf(
          'Table canonical identifier \'%s\' cannot be converted into a machine identifier%s',
          $canonicalName,
          $info['needs_prefix'] ? "; table prefix '{$this->tablePrefix}'" : '',
        ));
      }
      $lSize = (int) ($allowedLength / 2);
      $rSize = $allowedLength - $lSize;
      return $this->quote($prefix . substr($canonicalName, 0, $lSize) . $hash . substr($canonicalName, -$rSize));
    }
    return $this->quote($prefix . $canonicalName);
  }

}
