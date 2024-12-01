<?php

namespace Drupal\Core\Database\Event;

/**
 * Represents the execution of a method ensuring an indicated schema exists.
 */
class ExecuteMethodEnsuringSchemaEvent extends DatabaseEvent {

  protected mixed $closureExecutionResult;
  protected bool $closureExecutionSuccess;

  public function __construct(
    public readonly \Closure $execute,
    public readonly array $schema,
    public readonly bool $retryAfterSchemaEnsured = FALSE,
  ) {
    parent::__construct();
  }

  public function setResult(mixed $result): void {
    $this->closureExecutionResult = $result;
  }

  public function getResult(): mixed {
    return $this->closureExecutionResult;
  }

  public function setSuccess(bool $success): void {
    $this->closureExecutionSuccess = $success;
  }

  public function getSuccess(): bool {
    return $this->closureExecutionSuccess;
  }

}
