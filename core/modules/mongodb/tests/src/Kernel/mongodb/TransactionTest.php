<?php

namespace Drupal\Tests\mongodb\Kernel\mongodb;

use Drupal\KernelTests\Core\Database\DriverSpecificTransactionTestBase;

/**
 * Tests transaction for the MongoDB driver.
 *
 * @group Database
 */
class TransactionTest extends DriverSpecificTransactionTestBase {

  /**
   * {@inheritdoc}
   */
  public function testRollbackRootWithActiveSavepoint(): void {
    $this->markTestSkipped('The MongoDB database driver does not support nested transactions.');
  }

  /**
   * {@inheritdoc}
   */
  public function testRollbackSavepoint(): void {
    $this->markTestSkipped('The MongoDB database driver does not support nested transactions.');
  }

  /**
   * {@inheritdoc}
   */
  public function testRollbackSavepointWithLaterSavepoint(): void {
    $this->markTestSkipped('The MongoDB database driver does not support nested transactions.');
  }

  /**
   * {@inheritdoc}
   */
  public function testCommittedTransaction(): void {
    $this->markTestSkipped('The MongoDB database driver does not support nested transactions.');
  }

  /**
   * {@inheritdoc}
   */
  public function testTransactionWithDdlStatement(): void {
    $this->markTestSkipped('The MongoDB database driver does not support nested transactions.');
  }

  /**
   * {@inheritdoc}
   */
  public function testTransactionStacking(): void {
    $this->markTestSkipped('The MongoDB database driver does not support nested transactions.');
  }

  /**
   * Tests that transactions can continue to be used if a query fails.
   */
  public function testQueryFailureInTransaction(): void {
    $transaction = $this->connection->startTransaction('test_transaction');
    $this->connection->schema()->dropTable('test');

    // Test a failed query using the query() method.
    try {
      $this->connection->query('SELECT [age] FROM {test} WHERE [name] = :name', [':name' => 'David'])->fetchField();
      $this->fail('Using the query method should have failed.');
    }
    catch (\Exception $e) {
      // Just continue testing.
    }

    // Test a failed select query.
    try {
      $this->connection->select('test')
        ->fields('test', ['name'])
        ->execute();

      $this->fail('Select query should have failed.');
    }
    catch (\Exception $e) {
      // Just continue testing.
    }

    // Test a failed insert query.
    try {
      $this->connection->insert('test')
        ->fields([
          'name' => 'David',
          'age' => '24',
        ])
        ->execute();

      $this->fail('Insert query should have failed.');
    }
    catch (\Exception $e) {
      // Just continue testing.
    }

    // Test a failed update query.
    try {
      $this->connection->update('test')
        ->fields(['name' => 'Tiffany'])
        ->condition('id', 1)
        ->execute();

      $this->fail('Update query should have failed.');
    }
    catch (\Exception $e) {
      // Just continue testing.
    }

    // Test a failed delete query.
    try {
      $this->connection->delete('test')
        ->condition('id', 1)
        ->execute();

      $this->fail('Delete query should have failed.');
    }
    catch (\Exception $e) {
      // Just continue testing.
    }

//    // Test a failed merge query.
//    try {
//      $this->connection->merge('test')
//        ->key('job', 'Presenter')
//        ->fields([
//          'age' => '31',
//          'name' => 'Tiffany',
//        ])
//        ->execute();
//
//      $this->fail('Merge query should have failed.');
//    }
//    catch (\Exception $e) {
//      // Just continue testing.
//    }
//
//    // Test a failed upsert query.
//    try {
//      $this->connection->upsert('test')
//        ->key('job')
//        ->fields(['job', 'age', 'name'])
//        ->values([
//          'job' => 'Presenter',
//          'age' => 31,
//          'name' => 'Tiffany',
//        ])
//        ->execute();
//
//      $this->fail('Upsert query should have failed.');
//    }
//    catch (\Exception $e) {
//      // Just continue testing.
//    }
//
//    // Create the missing schema and insert a row. MongoDB has the table already
//    // created and DDL operations in multi-document transactions is something
//    // that MongoDB does not support.
//    if ($this->connection->driver() != 'mongodb') {
//      $this->installSchema('database_test', ['test']);
//    }
//
//    // Create the missing schema and insert a row. MongoDB has the table already
//    // created and DDL operations in multi-document transactions is something
//    // that MongoDB does not support.
//    try {
//      $this->installSchema('database_test', ['test']);
//    }
//    catch (SchemaObjectExistsException $e) {
//      // Do nothing.
//    }
//    $this->connection->insert('test')
//      ->fields([
//        'name' => 'David',
//        'age' => '24',
//      ])
//      ->execute();
//
//    // Commit the transaction.
//    unset($transaction);
//
//    $saved_age = $this->connection->query('SELECT [age] FROM {test} WHERE [name] = :name', [':name' => 'David'])->fetchField();
//    $this->assertEquals('24', $saved_age);
  }

  /**
   * {@inheritdoc}
   */
  public function testReleaseIntermediateSavepoint(): void {
    $this->markTestSkipped('The MongoDB database driver does not support nested transactions.');
  }

  /**
   * {@inheritdoc}
   */
  public function testCommitWithActiveSavepoint(): void {
    $this->markTestSkipped('The MongoDB database driver does not support nested transactions.');
  }

  /**
   * {@inheritdoc}
   */
  public function testTransactionName(): void {
    $this->markTestSkipped('The MongoDB database driver does not support nested transactions.');
  }

  /**
   * {@inheritdoc}
   */
  public function testConnectionDeprecations(): void {
    $this->markTestSkipped('The MongoDB database driver does not support nested transactions.');
  }

}
