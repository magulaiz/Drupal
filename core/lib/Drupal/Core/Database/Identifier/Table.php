<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Identifier;

/**
 * @todo fill in.
 */
class Table extends IdentifierBase {

  /**
   * @todo fill in.
   */
  public readonly ?string $schema;

  /**
   * @todo fill in.
   */
  public readonly ?string $database;

  /**
   * @todo fill in.
   */
  public readonly string $table;

  /**
   * @todo fill in.
   */
  public readonly bool $needsPrefix;

  /**
   * @todo fill in.
   */
  protected string $machineName;

  public function __construct(
    protected readonly IdentifierHandler $identifierHandler,
    public readonly string $identifier,
  ) {
    $parts = explode(".", $identifier);
    [$this->schema, $this->database, $this->table] = match (count($parts)) {
      1 => [
        NULL,
        NULL,
        $this->identifierHandler->tableEscapeName($parts[0]),
      ],
      2 => [
        NULL,
        $this->identifierHandler->tableEscapeName($parts[0]),
        $this->identifierHandler->tableEscapeName($parts[1]),
      ],
      3 => [
        $this->identifierHandler->tableEscapeName($parts[0]),
        $this->identifierHandler->tableEscapeName($parts[1]),
        $this->identifierHandler->tableEscapeName($parts[2]),
      ],
    };
    $this->needsPrefix = match (count($parts)) {
      1 => TRUE,
      default => FALSE,
    };
  }

  /**
   * @todo fill in.
   */
  public function machineName(bool $quoted = TRUE): string {
    if (!isset($this->machineName)) {
      $this->machineName = $this->identifierHandler->tableMachineName($this);
    }
    [$start_quote, $end_quote] = $this->identifierHandler->identifierQuotes;
    return $quoted ? $start_quote . str_replace(".", "$end_quote.$start_quote", $this->machineName) . $end_quote : $this->machineName;
  }

}
