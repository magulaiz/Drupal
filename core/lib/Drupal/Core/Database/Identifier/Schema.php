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
    IdentifierProcessorBase $identifierProcessor,
    string $identifier,
  ) {
    parent::__construct(
      $identifierProcessor,
      $identifier,
      $identifierProcessor->canonicalizeIdentifier($identifier, IdentifierType::Schema),
    );
  }

}
