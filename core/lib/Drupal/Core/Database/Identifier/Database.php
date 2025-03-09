<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Identifier;

/**
 * Handles a database identifier.
 *
 * In full namespaced tables, the identifier is defined as
 * <database>.<schema>.<table>.
 */
class Database extends IdentifierBase {

  public function __construct(
    IdentifierProcessorBase $identifierProcessor,
    string $identifier,
  ) {
    parent::__construct(
      $identifierProcessor,
      $identifier,
      $identifierProcessor->canonicalizeIdentifier($identifier, IdentifierType::Database),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function machineName(bool $quoted = TRUE): string {
    return $quoted ? $this->identifierProcessor->quote($this->canonicalName) : $this->canonicalName;
  }

}
