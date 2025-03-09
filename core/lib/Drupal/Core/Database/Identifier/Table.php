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
  public readonly bool $needsPrefix;

  /**
   * @todo fill in.
   */
  public readonly string $canonicalName;

  /**
   * @todo fill in.
   */
  protected string $machineName;

  public function __construct(
    IdentifierProcessorBase $identifierProcessor,
    string $identifier,
  ) {
    parent::__construct($identifierProcessor, $identifier);
    [$this->database, $this->schema, $this->canonicalName, $this->needsPrefix] = $this->identifierProcessor->parseTableIdentifier($identifier);
    if (strlen($this->needsPrefix ? $this->identifierProcessor->tablePrefix : '' . $this->canonicalName) > $this->identifierProcessor->getMaxLength(IdentifierType::Table)) {
      throw new IdentifierException(sprintf(
        'The machine length of the %s identifier \'%s\' exceeds the maximum allowed (%d)',
        IdentifierType::Table->value,
        $this->canonicalName,
        $this->identifierProcessor->getMaxLength(IdentifierType::Table),
      ));
    }
  }

  /**
   * @todo fill in.
   */
  public function machineName(bool $quoted = TRUE): string {
    if (!isset($this->machineName)) {
      $this->machineName = $this->identifierProcessor->tableMachineName($this);
    }
    [$start_quote, $end_quote] = $this->identifierProcessor->identifierQuotes;
    return $quoted ? $start_quote . str_replace(".", "$end_quote.$start_quote", $this->machineName) . $end_quote : $this->machineName;
  }

}
