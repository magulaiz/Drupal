<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Identifier;

/**
 * @todo fill in.
 */
class IdentifierHandler {

  /**
   * @var array{'identifier':array<string,array<string,string>>,'machine':array<string,array<string,string>>}
   */
  protected array $identifiers;

  public function __construct(
    public readonly IdentifierProcessorBase $identifierProcessor,
  ) {
  }

  /**
   * @todo fill in.
   */
  public function table(string|Table $tableIdentifier): Table {
    if ($tableIdentifier instanceof Table) {
      $tableIdentifier = $tableIdentifier->identifier;
    }
    if ($this->hasIdentifier($tableIdentifier, IdentifierType::Table)) {
      $table = $this->getIdentifier($tableIdentifier, IdentifierType::Table);
    }
    else {
      $table = new Table($this, $tableIdentifier);
      $this->setIdentifier($tableIdentifier, IdentifierType::Table, $table);
    }
    return $table;
  }

  /**
   * @todo fill in.
   */
  protected function setIdentifier(string $id, IdentifierType $type, IdentifierBase $identifier): void {
    $this->identifiers['identifier'][$id][$type->value] = $identifier;
  }

  /**
   * @todo fill in.
   */
  protected function hasIdentifier(string $id, IdentifierType $type): bool {
    return isset($this->identifiers['identifier'][$id][$type->value]);
  }

  /**
   * @todo fill in.
   */
  protected function getIdentifier(string $id, IdentifierType $type): IdentifierBase {
    return $this->identifiers['identifier'][$id][$type->value];
  }

}
