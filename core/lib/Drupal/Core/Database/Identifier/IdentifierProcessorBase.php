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
  public function parseTableIdentifier(string $identifier): array {
    $parts = explode(".", $identifier);
    [$database, $schema, $table] = match (count($parts)) {
      1 => [
        NULL,
        NULL,
        $this->tableEscapeName($parts[0]),
      ],
      2 => [
        NULL,
        $this->tableEscapeName($parts[0]),
        $this->tableEscapeName($parts[1]),
      ],
      3 => [
        $this->tableEscapeName($parts[0]),
        $this->tableEscapeName($parts[1]),
        $this->tableEscapeName($parts[2]),
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
  public function tableEscapeName(string $table): string {
    return preg_replace('/[^A-Za-z0-9_.]+/', '', $table);
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
