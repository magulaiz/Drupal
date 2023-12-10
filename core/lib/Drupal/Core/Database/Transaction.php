<?php

namespace Drupal\Core\Database;

/**
 * A wrapper class for creating and managing database transactions.
 *
 * Not all databases or database configurations support transactions. For
 * example, MySQL MyISAM tables do not. It is also easy to begin a transaction
 * and then forget to commit it, which can lead to connection errors when
 * another transaction is started.
 *
 * This class acts as a wrapper for transactions. To begin a transaction,
 * simply instantiate it. When the object goes out of scope and is destroyed
 * it will automatically commit. It also will check to see if the specified
 * connection supports transactions. If not, it will simply skip any transaction
 * commands, allowing user-space code to proceed normally. The only difference
 * is that rollbacks won't actually do anything.
 *
 * In the vast majority of cases, you should not instantiate this class
 * directly. Instead, call ->startTransaction(), from the appropriate connection
 * object.
 */
class Transaction {

  /**
   * The connection object for this transaction.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * A boolean value to indicate whether this transaction has been rolled back.
   *
   * @var bool
   *
   * @deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. There is
   *   no replacement.
   *
   * @see https://www.drupal.org/node/3381002
   */
  protected $rolledBack = FALSE;

  /**
   * The name of the transaction.
   *
   * This is used to label the transaction savepoint. It will be overridden to
   * 'drupal_transaction' if there is no transaction depth.
   *
   * @var string
   */
  protected $name;

  public function __construct(
    Connection $connection,
    $name = NULL,
    protected readonly string $id = '',
  ) {
    if ($connection->transactionManager()) {
      $this->connection = $connection;
      $this->name = $name;
      return;
    }
    // Start of BC layer.
    $this->connection = $connection;
    // If there is no transaction depth, then no transaction has started. Name
    // the transaction 'drupal_transaction'.
    // @phpstan-ignore-next-line
    if (!$depth = $connection->transactionDepth()) {
      $this->name = 'drupal_transaction';
    }
    // Within transactions, savepoints are used. Each savepoint requires a
    // name. So if no name is present we need to create one.
    elseif (!$name) {
      $this->name = 'savepoint_' . $depth;
    }
    else {
      $this->name = $name;
    }
    // @phpstan-ignore-next-line
    $this->connection->pushTransaction($this->name);
    // End of BC layer.
  }

  public function __destruct() {
    if ($this->connection->transactionManager()) {
      $this->connection->transactionManager()->purge($this->name, $this->id);
      return;
    }
    // Start of BC layer.
    // If we rolled back then the transaction would have already been popped.
    // @phpstan-ignore-next-line
    if (!$this->rolledBack) {
      // @phpstan-ignore-next-line
      $this->connection->popTransaction($this->name);
    }
    // End of BC layer.
  }

  /**
   * Retrieves the name of the transaction or savepoint.
   */
  public function name() {
    return $this->name;
  }

  /**
   * Commits the transaction.
   *
   * Depending on the state of the transaction stack, this leads to a COMMIT
   * operation (if this transaction is a root one), or to a RELEASE SAVEPOINT
   * operation (if this transaction is a savepoint one).
   */
  public function commit(): void {
    if (!$this->connection->transactionManager()) {
      @trigger_error('Calling ' . __METHOD__ . '() with no TransactionManager available is deprecated in drupal:10.3.0 and is removed from drupal:11.0.0. Ensure the database driver implements a TransactionManager. See https://www.drupal.org/node/7654321', E_USER_DEPRECATED);
      return;
    }
    $this->connection->transactionManager()->unpile($this->name, $this->id);
  }

  /**
   * Rolls back the transaction.
   *
   * Depending on the state of the transaction stack, this leads to a ROLLBACK
   * operation (if this transaction is a root one), or to a ROLLBACK TO
   * SAVEPOINT + a RELEASE SAVEPOINT operations (if this transaction is a
   * savepoint one).
   */
  public function rollBack() {
    if ($this->connection->transactionManager()) {
      $this->connection->transactionManager()->rollback($this->name, $this->id);
      return;
    }
    // Start of BC layer.
    // @phpstan-ignore-next-line
    $this->rolledBack = TRUE;
    // @phpstan-ignore-next-line
    $this->connection->rollBack($this->name);
    // End of BC layer.
  }

}
