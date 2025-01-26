<?php

declare(strict_types=1);

namespace Drupal\Core\Database;

use Drupal\Core\Database\Transaction\TransactionManagerInterface;

/**
 * Trait for transactional database functionality.
 */
trait TransactionalConnectionTrait {

  /**
   * The transaction manager.
   */
  protected TransactionManagerInterface $transactionManager;

  /**
   * Commits all the open transactions.
   *
   * @internal
   *   This method exists only to work around a bug caused by Drupal incorrectly
   *   relying on object destruction order to commit transactions. Xdebug 3.3.0
   *   changes the order of object destruction when the develop mode is enabled.
   */
  public function commitAll() {
    $manager = $this->transactionManager();
    if ($manager->inTransaction() && method_exists($manager, 'commitAll')) {
      $this->transactionManager()->commitAll();
    }
  }

  /**
   * Returns the transaction manager.
   *
   * @return \Drupal\Core\Database\Transaction\TransactionManagerInterface
   *   The transaction manager, or FALSE if not available.
   *
   * @throws \LogicException
   *   If the transaction manager is undefined or unavailable.
   */
  public function transactionManager(): TransactionManagerInterface {
    if (!isset($this->transactionManager)) {
      $this->transactionManager = $this->driverTransactionManager();
    }
    return $this->transactionManager;
  }

  /**
   * Returns a new instance of the driver's transaction manager.
   *
   * Database drivers must implement their own class extending from
   * \Drupal\Core\Database\Transaction\TransactionManagerBase, and instantiate
   * it here.
   *
   * @return \Drupal\Core\Database\Transaction\TransactionManagerInterface
   *   The transaction manager.
   *
   * @throws \LogicException
   *   If the transaction manager is undefined or unavailable.
   */
  // phpcs:ignore Drupal.Commenting.FunctionComment.InvalidNoReturn, Drupal.Commenting.FunctionComment.Missing
  protected function driverTransactionManager(): TransactionManagerInterface {
    throw new \LogicException('The database driver has no TransactionManager implementation');
  }

  /**
   * Determines if there is an active transaction open.
   *
   * @return bool
   *   TRUE if we're currently in a transaction, FALSE otherwise.
   */
  public function inTransaction() {
    return $this->transactionManager()->inTransaction();
  }

  /**
   * Returns a new DatabaseTransaction object on this connection.
   *
   * @param string $name
   *   (optional) The name of the savepoint.
   *
   * @return \Drupal\Core\Database\Transaction
   *   A Transaction object.
   *
   * @see \Drupal\Core\Database\Transaction
   */
  public function startTransaction($name = '') {
    return $this->transactionManager()->push($name);
  }

}
