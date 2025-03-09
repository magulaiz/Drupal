<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Identifier;

use Drupal\Component\Assertion\Inspector;

/**
 * @todo fill in.
 */
abstract class IdentifierProcessorBase {

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
  public function canonicalizeIdentifier(string $identifier, IdentifierType $type): string {
    return preg_replace('/[^A-Za-z0-9_]+/', '', $identifier);
  }

  /**
   * @todo fill in.
   */
  public function parseTableIdentifier(string $identifier): array {
    $parts = explode(".", $identifier);
    [$database, $schema, $table] = match (count($parts)) {
      1 => [
        NULL,
        NULL,
        $this->canonicalizeIdentifier($parts[0], IdentifierType::Table),
      ],
      2 => [
        NULL,
        new Schema($this, $parts[0]),
        $this->canonicalizeIdentifier($parts[1], IdentifierType::Table),
      ],
      3 => [
        new Database($this, $parts[0]),
        new Schema($this, $parts[1]),
        $this->canonicalizeIdentifier($parts[2], IdentifierType::Table),
      ],
    };
    $needsPrefix = match (count($parts)) {
      1 => TRUE,
      default => FALSE,
    };
    return [$database, $schema, $table, $needsPrefix];
  }

  /**
   * @todo fill in.
   */
  public function tableMachineName(Table $table): string {
    $tableName = $table->needsPrefix ? $this->tablePrefix . $table->canonicalName : $table->canonicalName;
    if ($table->database) {
      return implode('.', [$table->database, $table->schema, $tableName]);
    }
    elseif ($table->schema) {
      return implode('.', [$table->schema, $tableName]);
    }
    return $tableName;
  }

}
