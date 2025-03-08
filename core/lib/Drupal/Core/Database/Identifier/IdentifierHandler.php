<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Identifier;

/**
 * @todo fill in.
 */
class IdentifierHandler {

  /**
   * @var array{'identifier':array<string,array<string,string>>,'platform':array<string,array<string,string>>}
   */
  protected array $identifiers;

  /**
   * @var array{'identifier':array<string,array<string,string>>,'platform':array<string,array<string,string>>}
   */
  protected array $aliases;

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
      $this->setIdentifier1($tableIdentifier, IdentifierType::Table, $table);
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
    if ($table->schema) {
      return implode('.', [$table->schema, $table->database, $tableName]);
    }
    elseif ($table->database) {
      return implode('.', [$table->database, $tableName]);
    }
    return $tableName;
  }

  /**
   * @todo fill in.
   */
  protected function setIdentifier(string $identifier, string $platform_identifier, IdentifierType $type, bool $isAlias): void {
    if (!$isAlias) {
      $this->identifiers['identifier'][$identifier][$type->value] = $platform_identifier;
      $this->identifiers['platform'][$platform_identifier][$type->value] = $identifier;
    }
    else {
      $this->aliases['identifier'][$identifier][$type->value] = $platform_identifier;
      $this->aliases['platform'][$platform_identifier][$type->value] = $identifier;
    }
  }

  /**
   * @todo fill in.
   */
  protected function setIdentifier1(string $id, IdentifierType $type, IdentifierBase $identifier): void {
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
  public function getPlatformIdentifierName(string $original_name, bool $quoted = TRUE): string {
    if (!$this->hasIdentifier($original_name, IdentifierType::Generic)) {
      $this->setIdentifier($original_name, $this->resolvePlatformGenericIdentifier($original_name), IdentifierType::Generic, FALSE);
    }
    [$start_quote, $end_quote] = $this->identifierQuotes;
    $identifier = $this->identifiers['identifier'][$original_name][IdentifierType::Generic->value];
    return $quoted ? $start_quote . $identifier . $end_quote : $identifier;
  }

  /**
   * @todo fill in.
   */
  public function getPlatformDatabaseName(string $original_name, bool $quoted = TRUE): string {
    $original_name = (string) preg_replace('/[^A-Za-z0-9_]+/', '', $original_name);
    if (!$this->hasIdentifier($original_name, IdentifierType::Database)) {
      $this->setIdentifier($original_name, $this->resolvePlatformDatabaseIdentifier($original_name), IdentifierType::Database, FALSE);
    }
    [$start_quote, $end_quote] = $this->identifierQuotes;
    return $quoted ?
      $start_quote . $this->identifiers['identifier'][$original_name][IdentifierType::Database->value] . $end_quote :
      $this->identifiers['identifier'][$original_name][IdentifierType::Database->value];
  }

  /**
   * @todo fill in.
   */
  public function getPlatformColumnName(string $original_name, bool $quoted = TRUE): string {
    if ($original_name === '') {
      return '';
    }
    $original_name = (string) preg_replace('/[^A-Za-z0-9_.]+/', '', $original_name);
    if (!$this->hasIdentifier($original_name, IdentifierType::Column)) {
      $this->setIdentifier($original_name, $this->resolvePlatformColumnIdentifier($original_name), IdentifierType::Column, FALSE);
    }
    // Sometimes fields have the format table_alias.field. In such cases
    // both identifiers should be quoted, for example, "table_alias"."field".
    [$start_quote, $end_quote] = $this->identifierQuotes;
    return $quoted ?
      $start_quote . str_replace(".", "$end_quote.$start_quote", $this->identifiers['identifier'][$original_name][IdentifierType::Column->value]) . $end_quote :
      $this->identifiers['identifier'][$original_name][IdentifierType::Column->value];
  }

  /**
   * @todo fill in.
   */
  public function getPlatformAliasName(string $original_name, IdentifierType $type = IdentifierType::Generic, bool $quoted = TRUE): string {
    $original_name = (string) preg_replace('/[^A-Za-z0-9_]+/', '', $original_name);
    if ($original_name[0] === $this->identifierQuotes[0]) {
      $original_name = substr($original_name, 1, -1);
    }
    if (!$this->hasIdentifier($original_name, IdentifierType::Alias)) {
      $this->setIdentifier($original_name, $this->resolvePlatformGenericIdentifier($original_name), $type, TRUE);
    }
    [$start_quote, $end_quote] = $this->identifierQuotes;
    $alias = $this->aliases['identifier'][$original_name][$type->value] ?? $this->identifiers['identifier'][$original_name][0];
    return $quoted ? $start_quote . $alias . $end_quote : $alias;
  }

  /**
   * @todo fill in.
   */
  protected function resolvePlatformGenericIdentifier(string $identifier): string {
    return $identifier;
  }

  /**
   * @todo fill in.
   */
  protected function resolvePlatformDatabaseIdentifier(string $identifier): string {
    return $identifier;
  }

  /**
   * @todo fill in.
   */
  protected function resolvePlatformTableIdentifier(string $identifier): string {
    return $identifier;
  }

  /**
   * @todo fill in.
   */
  protected function resolvePlatformColumnIdentifier(string $identifier): string {
    return $identifier;
  }

  /**
   * @todo fill in.
   */
  protected function resolvePlatformAliasIdentifier(string $identifier, int $type = 0): string {
    return $identifier;
  }

}
