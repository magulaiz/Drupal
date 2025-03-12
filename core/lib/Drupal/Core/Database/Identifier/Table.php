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
  public readonly ?Database $database;

  /**
   * @todo fill in.
   */
  public readonly ?Schema $schema;

  /**
   * @todo fill in.
   */
  public readonly bool $needsPrefix;

  public function __construct(
    IdentifierHandlerBase $identifierHandler,
    string $identifier,
  ) {
    $parts = $identifierHandler->parseTableIdentifier($identifier);

    $canonicalName = $identifierHandler->canonicalize($parts['table'], IdentifierType::Table);
    $machineName = $identifierHandler->resolveForMachine($canonicalName, $parts, IdentifierType::Table);
    parent::__construct($identifier, $canonicalName, $machineName);

    $this->database = $parts['database'];
    $this->schema = $parts['schema'];
    $this->needsPrefix = $parts['needs_prefix'];
  }

  /**
   * {@inheritdoc}
   */
  public function canonical(): string {
    $ret = isset($this->database) ? $this->database->canonical() . '.' : '';
    $ret .= isset($this->schema) ? $this->schema->canonical() . '.' : '';
    $ret .= $this->canonicalName;
    return $ret;
  }

  /**
   * {@inheritdoc}
   */
  public function forMachine(): string {
    $ret = isset($this->database) ? $this->database->forMachine() . '.' : '';
    $ret .= isset($this->schema) ? $this->schema->forMachine() . '.' : '';
    $ret .= $this->machineName;
    return $ret;
  }

}
