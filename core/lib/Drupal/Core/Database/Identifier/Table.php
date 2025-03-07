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
  protected string $escapedName;

  /**
   * @todo fill in.
   */
  protected string $machineName;

  public function __construct(
    protected readonly IdentifierHandler $identifierHandler,
    public readonly string $name,
  ) {
  }

  /**
   * @todo fill in.
   */
  public function escapedName(): string {
    @trigger_error(__METHOD__ . "() is deprecated in drupal:11.9.0 and is removed from drupal:12.0.0. This is no longer used. See https://www.drupal.org/node/7654312", E_USER_DEPRECATED);
    return $this->escape();
  }

  /**
   * @todo fill in.
   */
  public function escape(): string {
    if (!isset($this->escapedName)) {
      $this->escapedName = $this->identifierHandler->tableEscapeName($this);
    }
    return $this->escapedName;
  }

  /**
   * @todo fill in.
   */
  public function machineName(bool $prefixed = TRUE, bool $quoted = TRUE): string {
    if (!isset($this->machineName)) {
      $this->machineName = $this->identifierHandler->tableMachineName($this);
    }
    [$start_quote, $end_quote] = $this->identifierHandler->identifierQuotes;
    $unquotedMachineName = $prefixed ? $this->identifierHandler->tablePrefix . $this->machineName : $this->machineName;
    return $quoted ? $start_quote . str_replace(".", "$end_quote.$start_quote", $unquotedMachineName) . $end_quote : $unquotedMachineName;
  }

}
