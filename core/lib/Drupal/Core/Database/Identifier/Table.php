<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Identifier;

use Drupal\Core\Database\Exception\IdentifierException;

/**
 * @todo fill in.
 */
class Table extends IdentifierBase {

  /**
   * @todo fill in.
   */
  public readonly ?string $database;

  /**
   * @todo fill in.
   */
  public readonly ?string $schema;

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
    IdentifierHandler $identifierHandler,
    string $identifier,
  ) {
    parent::__construct($identifierHandler, $identifier);
    $parts = explode(".", $identifier);
    [$this->database, $this->schema, $this->table] = match (count($parts)) {
      1 => [
        NULL,
        NULL,
        $this->identifierHandler->identifierProcessor->tableEscapeName($parts[0]),
      ],
      2 => [
        NULL,
        $this->identifierHandler->identifierProcessor->tableEscapeName($parts[0]),
        $this->identifierHandler->identifierProcessor->tableEscapeName($parts[1]),
      ],
      3 => [
        $this->identifierHandler->identifierProcessor->tableEscapeName($parts[0]),
        $this->identifierHandler->identifierProcessor->tableEscapeName($parts[1]),
        $this->identifierHandler->identifierProcessor->tableEscapeName($parts[2]),
      ],
    };
    $this->needsPrefix = match (count($parts)) {
      1 => TRUE,
      default => FALSE,
    };
    if (strlen($this->needsPrefix ? $this->identifierHandler->identifierProcessor->tablePrefix : '' . $this->table) > $this->identifierHandler->identifierProcessor->getMaxLength(IdentifierType::Table)) {
      throw new IdentifierException(sprintf(
        'The machine length of the %s identifier \'%s\' exceeds the maximum allowed (%d)',
        IdentifierType::Table->value,
        $this->table,
        $this->identifierHandler->identifierProcessor->getMaxLength(IdentifierType::Table),
      ));
    }
  }

  /**
   * @todo fill in.
   */
  public function machineName(bool $quoted = TRUE): string {
    if (!isset($this->machineName)) {
      $this->machineName = $this->identifierHandler->identifierProcessor->tableMachineName($this);
    }
    [$start_quote, $end_quote] = $this->identifierHandler->identifierProcessor->identifierQuotes;
    return $quoted ? $start_quote . str_replace(".", "$end_quote.$start_quote", $this->machineName) . $end_quote : $this->machineName;
  }

}
