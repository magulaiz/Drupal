<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Identifier;

use Drupal\Component\Assertion\Inspector;

/**
 * @todo fill in.
 */
class IdentifierHandler {

  /**
   * @var array{'identifier':array<string,array<string,string>>,'machine':array<string,array<string,string>>}
   */
  protected array $identifiers;

  /**
   * Constructs an IdentifierHandler object.
   *
   * @param string $tablePrefix
   *   The table prefix to be used by the database connection.
   * @param array{0:string, 1:string} $identifierQuotes
   *   The identifier quote characters for the database type. An array
   *   containing the start and end identifier quote characters for the
   *   database type. The ANSI SQL standard identifier quote character is a
   *   double quotation mark.
   */
  public function __construct(
    public readonly string $tablePrefix,
    public readonly array $identifierQuotes = ['"', '"'],
  ) {
    assert(count($this->identifierQuotes) === 2 && Inspector::assertAllStrings($this->identifierQuotes), __CLASS__ . '::$identifierQuotes must contain 2 string values');
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
  public function tableEscapeName(string $table): string {
    return preg_replace('/[^A-Za-z0-9_.]+/', '', $table);
  }

  /**
   * @todo fill in.
   */
  public function tableMachineName(Table $table): string {
    $tableName = $table->needsPrefix ? $this->tablePrefix . $table->table : $table->table;
    if ($table->database) {
      return implode('.', [$table->database, $table->schema, $tableName]);
    }
    elseif ($table->schema) {
      return implode('.', [$table->schema, $tableName]);
    }
    return $tableName;
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
