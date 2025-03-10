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
    IdentifierHandlerBase $identifierHandler,
    string $identifier,
  ) {
    parent::__construct(
      $identifierHandler,
      $identifier,
      $identifierHandler->canonicalizeIdentifier($identifier, IdentifierType::Database),
    );
  }

}
