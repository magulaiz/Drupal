<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Identifier;

/**
 * Handles a schema identifier.
 *
 * In full namespaced tables, the identifier is defined as
 * <database>.<schema>.<table>.
 */
class Schema extends IdentifierBase {

  public function __construct(
    IdentifierHandlerBase $identifierHandler,
    string $identifier,
  ) {
    $canonicalName = $identifierHandler->canonicalize($identifier, IdentifierType::Schema);
    $machineName = $identifierHandler->resolveForMachine($canonicalName, [], IdentifierType::Schema);
    parent::__construct($identifier, $canonicalName, $machineName);
  }

}
