<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Identifier;

use Drupal\Component\Assertion\Inspector;
use Drupal\Core\Database\Exception\IdentifierException;

/**
 * @todo fill in.
 */
abstract class IdentifierHandlerBase {

  /**
   * @var array{'identifier':array<string,array<string,string>>,'machine':array<string,array<string,string>>}
   */
  protected array $identifiers;

  /**
   * Constructor.
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
  public function database(string|Database $identifier): Database {
    if ($identifier instanceof Database) {
      $identifier = $identifier->identifier;
    }
    if ($this->hasIdentifier($identifier, IdentifierType::Database)) {
      $database = $this->getIdentifier($identifier, IdentifierType::Database);
    }
    else {
      $database = new Database($this, $identifier);
      $this->setIdentifier($identifier, IdentifierType::Database, $database);
    }
    return $database;
  }

  /**
   * @todo fill in.
   */
  public function schema(string|Schema $identifier): Schema {
    if ($identifier instanceof Schema) {
      $identifier = $identifier->identifier;
    }
    if ($this->hasIdentifier($identifier, IdentifierType::Schema)) {
      $schema = $this->getIdentifier($identifier, IdentifierType::Schema);
    }
    else {
      $schema = new Schema($this, $identifier);
      $this->setIdentifier($identifier, IdentifierType::Schema, $schema);
    }
    return $schema;
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

  /**
   * @todo fill in.
   */
  abstract public function getMaxLength(IdentifierType $type): int;

  /**
   * @todo fill in.
   */
  public function quote(string $value): string {
    return $this->identifierQuotes[0] . $value . $this->identifierQuotes[1];
  }

  /**
   * @todo fill in.
   */
  public function canonicalize(string $identifier, IdentifierType $type): string {
    $canonicalName = preg_replace('/[^A-Za-z0-9_]+/', '', $identifier);
    $canonicalNameLength = strlen($canonicalName);
    if ($canonicalNameLength > $this->getMaxLength($type) || $canonicalNameLength === 0) {
      throw new IdentifierException(sprintf(
        'The length of the %s identifier \'%s\' once canonicalized to \'%s\' is invalid (maximum allowed: %d)',
        $type->value,
        $identifier,
        $canonicalName,
        $this->getMaxLength($type),
      ));
    }
    return $canonicalName;
  }

  /**
   * @todo fill in.
   */
  public function resolveForMachine(string $canonicalName, IdentifierType $type): string {
    return match ($type) {
      IdentifierType::Table => $this->resolveTableForMachine($canonicalName),
      default => $this->quote($canonicalName),
    };
  }

  /**
   * @todo fill in.
   */
  protected function resolveTableForMachine(string $canonicalName): string {
    if (strlen($this->tablePrefix . $canonicalName) > $this->getMaxLength(IdentifierType::Table)) {
      throw new IdentifierException(sprintf(
        'The machine length of the %s canonicalized identifier \'%s\' once table prefix \'%s\' is added is invalid (maximum allowed: %d)',
        IdentifierType::Table->value,
        $canonicalName,
        $this->tablePrefix,
        $this->getMaxLength($type),
      ));
    }
    return $this->quote($this->tablePrefix ? $this->tablePrefix . $canonicalName : $canonicalName);
  }

  /**
   * @todo fill in.
   */
  public function parseTableIdentifier(string $identifier): array {
    $parts = explode(".", $identifier);
    [$database, $schema, $table] = match (count($parts)) {
      1 => [NULL, NULL, $parts[0]],
      2 => [NULL, $this->schema($parts[0]), $parts[1]],
      3 => [$this->database($parts[0]), $this->schema($parts[1]), $parts[2]],
    };
    if ($this->tablePrefix !== '') {
      $needsPrefix = match (count($parts)) {
        1 => TRUE,
        default => FALSE,
      };
    }
    else {
      $needsPrefix = FALSE;
    }
    return [
      'database' => $database,
      'schema' => $schema,
      'table' => $table,
      'needs_prefix' => $needsPrefix,
    ];
  }

}
