<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Event;

/**
 * Represents the execution of a method enforcing an indicated schema exists.
 */
final class ExecuteMethodEnsuringSchemaEvent extends DatabaseEvent {

  /**
   * Value returned by the execution of the callback.
   */
  protected mixed $closureExecutionResult;

  /**
   * Indicates if the callback execution was successful or not.
   */
  protected bool $closureExecutionSuccess;

  /**
   * Constructor.
   *
   * @param \Closure $execute
   *   The callback to be executed.
   * @param array<string,array<string,mixed>> $schema
   *   A database schema specification, with table name as key and schema
   *   array as value.
   * @param bool $retryAfterSchemaEnsured
   *   (Optional) If TRUE, the callback is executed again after the first
   *   execution failed, and the schema enforcement was successful. Defaults to
   *   FALSE.
   */
  public function __construct(
    public readonly \Closure $execute,
    public readonly array $schema,
    public readonly bool $retryAfterSchemaEnsured = FALSE,
  ) {
    parent::__construct();
  }

  /**
   * Stores the value returned by the execution of the callback.
   *
   * @param mixed $result
   *   The value returned by the execution of the callback.
   */
  public function setResult(mixed $result): void {
    $this->closureExecutionResult = $result;
  }

  /**
   * Gets the value returned by the execution of the callback.
   *
   * @return mixed
   *   The value returned by the execution of the callback.
   */
  public function getResult(): mixed {
    return $this->closureExecutionResult;
  }

  /**
   * Stores the success of the execution of the callback.
   *
   * @param bool $success
   *   The success of the execution of the callback.
   */
  public function setSuccess(bool $success): void {
    $this->closureExecutionSuccess = $success;
  }

  /**
   * Stores the success of the execution of the callback.
   *
   * @return bool
   *   The success of the execution of the callback.
   */
  public function getSuccess(): bool {
    return $this->closureExecutionSuccess;
  }

}
