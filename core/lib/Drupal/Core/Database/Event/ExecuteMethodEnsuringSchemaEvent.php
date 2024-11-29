<?php

namespace Drupal\Core\Database\Event;

/**
 * Represents the execution of a method ensuring an indicated schema exists.
 */
class ExecuteMethodEnsuringSchemaEvent extends DatabaseEvent {

  protected mixed $callMethodResult;

  public function __construct(
    public readonly \Closure $execute,
    public readonly array $schema,
    public readonly bool $retryAfterSchemaEnsured = FALSE,
  ) {
    parent::__construct();
  }

  public function setResult(mixed $result): void {
    $this->callMethodResult = $result;
  }

  public function getResult(): mixed {
    return $this->callMethodResult;
  }

}
