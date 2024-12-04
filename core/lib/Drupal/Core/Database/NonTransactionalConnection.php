<?php

declare(strict_types=1);

namespace Drupal\Core\Database;

use Drupal\Core\Database\Exception\TransactionsNotAllowedException;
use Drupal\Core\Database\Transaction\TransactionManagerInterface;

/**
 * A decorator class wrapping a connection, not supporting transactions.
 *
 * @internal
 */
final class NonTransactionalConnection implements DatabaseConnectionInterface {

  /**
   * Constructor.
   *
   * @param Connection $wrappedConnection
   *   Database connection to wrap calls.
   */
  public function __construct(protected Connection $wrappedConnection) {}

  /**
   * {@inheritdoc}
   */
  public function transactionManager(): TransactionManagerInterface {
    throw new TransactionsNotAllowedException();
  }

  /**
   * {@inheritdoc}
   */
  public function inTransaction(): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function startTransaction($name = '') {
    throw new TransactionsNotAllowedException();
  }

  /**
   * Opens a client connection.
   *
   * @param array $connection_options
   *   The database connection settings array.
   *
   * @return object
   *   A client connection object.
   */
  public static function open(array &$connection_options = []) {
    throw new \RuntimeException(sprintf('%s is a wrapper only around existing connection objects.', __CLASS__));
  }

  /**
   * {@inheritdoc}
   */
  public function supportsTransactionalDDL() {
    return FALSE;
  }

  /**
   * Passes through all unknown calls onto the decorated connection.
   *
   * @param string $method
   *   The method to call on the decorated connection.
   * @param array $args
   *   The arguments to send to the method.
   *
   * @return mixed
   *   The method result.
   */
  public function __call(string $method, array $args) {
    return call_user_func_array([$this->wrappedConnection, $method], $args);
  }

}
