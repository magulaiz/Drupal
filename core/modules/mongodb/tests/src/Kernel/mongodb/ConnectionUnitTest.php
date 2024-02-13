<?php

namespace Drupal\Tests\mongodb\Kernel\mongodb;

use Drupal\KernelTests\Core\Database\DriverSpecificConnectionUnitTestBase;
use Drupal\mongodb\Driver\Database\mongodb\Database;

// cspell:ignore inprog

/**
 * MySQL-specific connection unit tests.
 *
 * @group Database
 */
class ConnectionUnitTest extends DriverSpecificConnectionUnitTestBase {

  /**
   * {@inheritdoc}
   */
  protected function getConnectionId(): int {
    $currentOp = Database::getAdminConnection()->getConnection()->command(
      ['currentOp' => 1, '$ownOps' => 1],
      ['session' => $this->connection->getMongodbSession()],
    )->toArray()[0];

    return $currentOp->inprog[0]->connectionId ?? 0;
  }

  /**
   * Get the process list from the MongoDB admin connection.
   *
   * @return array
   *   The list of connection IDs.
   */
  protected function getProcessList() {
    $processList = [];

    $currentOp = Database::getAdminConnection()->getConnection()->command(
      ['currentOp' => 1],
      ['session' => $this->connection->getMongodbSession()],
    )->toArray()[0];

    foreach ($currentOp->inprog as $instance) {
      if (isset($instance->connectionId)) {
        $processList[] = $instance->connectionId;
      }
    }

    return $processList;
  }

  /**
   * {@inheritdoc}
   */
  protected function assertConnection(int $id): void {
    $this->assertContains($id, $this->getProcessList());
  }

  /**
   * Asserts that a connection ID does not exist.
   *
   * @param int $id
   *   The connection ID to verify.
   *
   * @internal
   */
  protected function assertNoConnection(int $id): void {
    // The MongoDB driver is designed to leave connections open, and there is no
    // method of turning that off. Therefor trying to assert that a connection
    // is closed will not work.
    // @see https://github.com/mongodb/mongo-php-driver/issues/393
    // $this->assertNotContains($id, $this->getProcessList());
  }

  /**
   * Tests pdo options override.
   */
  public function testConnectionOpen() {
    $this->markTestSkipped('The MongoDB database driver does not support PDO.');
  }

  /**
   * Returns a set of queries specific for MySQL.
   */
  protected function getQuery(): array {
    return [
      'show_tables' => 'SELECT COUNT(*) FROM {test}',
    ];
  }

}
