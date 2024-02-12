<?php

declare(strict_types=1);

namespace Drupal\mongodb\Driver\Database\mongodb;

use Drupal\Core\Database\Transaction;
use Drupal\Core\Database\Transaction\TransactionManagerBase;
use MongoDB\Driver\Exception\CommandException;

/**
 * MongoDB implementation of TransactionManagerInterface.
 */
class TransactionManager extends TransactionManagerBase {

  /**
   * {@inheritdoc}
   */
  public function push(string $name = ''): Transaction {
    if ($this->stackDepth() > 0) {
      throw new \LogicException('The transaction has already been started. MongoDB does not support nested transactions.');
    }

    return parent::push($name);
  }

  /**
   * {@inheritdoc}
   */
  protected function beginClientTransaction(): bool {
    $this->connection->getMongodbSession()->startTransaction();

    return TRUE;
  }

  public function inTransaction(): bool {
    return (bool) $this->connection->getMongodbSession() && $this->connection->getMongodbSession()->isInTransaction() && parent::inTransaction();
  }

  /**
   * {@inheritdoc}
   */
  protected function addClientSavepoint(string $name): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function rollbackClientSavepoint(string $name): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function releaseClientSavepoint(string $name): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function rollbackClientTransaction(): bool {
    try {
      $this->connection->getMongodbSession()->abortTransaction();
    }
    catch (CommandException $e) {
      // Do nothing.
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function commitClientTransaction(): bool {
    try {
      $this->connection->getMongodbSession()->commitTransaction();
    }
    catch (CommandException $e) {
      // Do nothing.
      return FALSE;
    }

    return TRUE;
  }

}
