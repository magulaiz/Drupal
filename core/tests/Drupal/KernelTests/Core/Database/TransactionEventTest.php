<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Database;

use Drupal\Core\Database\Event\TransactionBeginEvent;

/**
 * Tests the Transaction events.
 *
 * @group Database
 */
class TransactionEventTest extends DatabaseTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->connection->transactionManager()->enableEvents();
  }

  /**
   * Tests transaction beginning.
   */
  public function testTransactionBegin(): void {
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage("default default drupal_transaction");
    $tx = $this->connection->startTransaction();
  }

  /**
   * Tests adding a savepoint.
   */
  public function testTransactionSavepoint(): void {
    $this->connection->transactionManager()->disableEvents([
      TransactionBeginEvent::class,
    ]);
    $tx = $this->connection->startTransaction();
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage("default default savepoint_1 drupal_transaction");
    $savepoint = $this->connection->startTransaction();
  }

}
