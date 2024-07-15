<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Database;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\Condition;

// cspell:ignore gianna

/**
 * Tests of the core database system.
 *
 * @group Database
 */
class ConnectionTest extends DatabaseTestBase {

  /**
   * Tests that connections return appropriate connection objects.
   */
  public function testConnectionRouting(): void {
    // Clone the primary credentials to a replica connection.
    // Note this will result in two independent connection objects that happen
    // to point to the same place.
    $connection_info = Database::getConnectionInfo(Database::DEFAULT_KEY);
    Database::addConnectionInfo(Database::DEFAULT_KEY, Database::REPLICA_TARGET, $connection_info[Database::DEFAULT_TARGET]);

    $db1 = Database::getConnection(Database::DEFAULT_TARGET, Database::DEFAULT_KEY);
    $db2 = Database::getConnection(Database::REPLICA_TARGET, Database::DEFAULT_KEY);

    $this->assertNotNull($db1, 'default connection is a real connection object.');
    $this->assertNotNull($db2, 'replica connection is a real connection object.');
    $this->assertNotSame($db1, $db2, 'Each target refers to a different connection.');

    // Try to open those targets another time, that should return the same objects.
    $db1b = Database::getConnection(Database::DEFAULT_TARGET, Database::DEFAULT_KEY);
    $db2b = Database::getConnection(Database::REPLICA_TARGET, Database::DEFAULT_KEY);
    $this->assertSame($db1, $db1b, 'A second call to getConnection() returns the same object.');
    $this->assertSame($db2, $db2b, 'A second call to getConnection() returns the same object.');

    // Try to open an unknown target.
    $unknown_target = $this->randomMachineName();
    $db3 = Database::getConnection($unknown_target, Database::DEFAULT_KEY);
    $this->assertNotNull($db3, 'Opening an unknown target returns a real connection object.');
    $this->assertSame($db1, $db3, 'An unknown target opens the default connection.');

    // Try to open that unknown target another time, that should return the same object.
    $db3b = Database::getConnection($unknown_target, Database::DEFAULT_KEY);
    $this->assertSame($db3, $db3b, 'A second call to getConnection() returns the same object.');
  }

  /**
   * Tests that connections return appropriate connection objects.
   */
  public function testConnectionRoutingOverride(): void {
    // Clone the primary credentials to a replica connection.
    // Note this will result in two independent connection objects that happen
    // to point to the same place.
    $connection_info = Database::getConnectionInfo(Database::DEFAULT_KEY);
    Database::addConnectionInfo(Database::DEFAULT_KEY, Database::REPLICA_TARGET, $connection_info[Database::DEFAULT_TARGET]);

    Database::ignoreTarget(Database::DEFAULT_KEY, Database::REPLICA_TARGET);

    $db1 = Database::getConnection(Database::DEFAULT_TARGET, Database::DEFAULT_KEY);
    $db2 = Database::getConnection(Database::REPLICA_TARGET, Database::DEFAULT_KEY);

    $this->assertSame($db1, $db2, 'Both targets refer to the same connection.');
  }

  /**
   * Tests the closing of a database connection.
   */
  public function testConnectionClosing(): void {
    // Open the default target so we have an object to compare.
    $db1 = Database::getConnection(Database::DEFAULT_TARGET, Database::DEFAULT_KEY);

    // Try to close the default connection, then open a new one.
    Database::closeConnection(Database::DEFAULT_TARGET, Database::DEFAULT_KEY);
    $db2 = Database::getConnection(Database::DEFAULT_TARGET, Database::DEFAULT_KEY);

    // Opening a connection after closing it should yield an object different than the original.
    $this->assertNotSame($db1, $db2, 'Opening the default connection after it is closed returns a new object.');
  }

  /**
   * Tests the connection options of the active database.
   */
  public function testConnectionOptions(): void {
    $connection_info = Database::getConnectionInfo(Database::DEFAULT_KEY);

    // Be sure we're connected to the default database.
    $db = Database::getConnection(Database::DEFAULT_TARGET, Database::DEFAULT_KEY);
    $connectionOptions = $db->getConnectionOptions();

    // In the MySQL driver, the port can be different, so check individual
    // options.
    $this->assertEquals($connection_info[Database::DEFAULT_TARGET]['driver'], $connectionOptions['driver'], 'The default connection info driver matches the current connection options driver.');
    $this->assertEquals($connection_info[Database::DEFAULT_TARGET]['database'], $connectionOptions['database'], 'The default connection info database matches the current connection options database.');

    // Set up identical replica and confirm connection options are identical.
    Database::addConnectionInfo(Database::DEFAULT_KEY, Database::REPLICA_TARGET, $connection_info[Database::DEFAULT_TARGET]);
    $db2 = Database::getConnection(Database::REPLICA_TARGET, Database::DEFAULT_KEY);
    $connectionOptions2 = $db2->getConnectionOptions();

    // Get a fresh copy of the default connection options.
    $connectionOptions = $db->getConnectionOptions();
    $this->assertSame($connectionOptions2, $connectionOptions, 'The default and replica connection options are identical.');

    // Set up a new connection with different connection info.
    $test = $connection_info[Database::DEFAULT_TARGET];
    $test['database'] .= 'test';
    Database::addConnectionInfo('test', Database::DEFAULT_TARGET, $test);
    $connection_info = Database::getConnectionInfo('test');

    // Get a fresh copy of the default connection options.
    $connectionOptions = $db->getConnectionOptions();
    $this->assertNotEquals($connection_info[Database::DEFAULT_TARGET]['database'], $connectionOptions['database'], 'The test connection info database does not match the current connection options database.');
  }

  /**
   * Tests per-table prefix connection option.
   */
  public function testPerTablePrefixOption(): void {
    $connection_info = Database::getConnectionInfo(Database::DEFAULT_KEY);
    $new_connection_info = $connection_info[Database::DEFAULT_TARGET];
    $new_connection_info['prefix'] = [
      'default' => $connection_info[Database::DEFAULT_TARGET]['prefix'],
      'test_table' => $connection_info[Database::DEFAULT_TARGET]['prefix'] . '_bar',
    ];
    Database::addConnectionInfo(Database::DEFAULT_KEY, 'foo', $new_connection_info);
    $this->expectException(\AssertionError::class);
    $foo_connection = Database::getConnection('foo', Database::DEFAULT_KEY);
  }

  /**
   * Tests the prefix connection option in array form.
   */
  public function testPrefixArrayOption(): void {
    $connection_info = Database::getConnectionInfo(Database::DEFAULT_KEY);
    $new_connection_info = $connection_info[Database::DEFAULT_TARGET];
    $new_connection_info['prefix'] = [
      'default' => $connection_info[Database::DEFAULT_TARGET]['prefix'],
    ];
    Database::addConnectionInfo(Database::DEFAULT_KEY, 'foo', $new_connection_info);
    $this->expectException(\AssertionError::class);
    $foo_connection = Database::getConnection('foo', Database::DEFAULT_KEY);
  }

  /**
   * Ensure that you cannot execute multiple statements in a query.
   */
  public function testMultipleStatementsQuery(): void {
    $this->expectException(\InvalidArgumentException::class);
    Database::getConnection(Database::DEFAULT_TARGET, Database::DEFAULT_KEY)->query('SELECT * FROM {test}; SELECT * FROM {test_people}');
  }

  /**
   * Ensure that you cannot prepare multiple statements.
   */
  public function testMultipleStatements(): void {
    $this->expectException(\InvalidArgumentException::class);
    Database::getConnection(Database::DEFAULT_TARGET, Database::DEFAULT_KEY)->prepareStatement('SELECT * FROM {test}; SELECT * FROM {test_people}', []);
  }

  /**
   * Tests that the method ::condition() returns a Condition object.
   */
  public function testCondition(): void {
    $connection = Database::getConnection(Database::DEFAULT_TARGET, Database::DEFAULT_KEY);
    $namespace = (new \ReflectionObject($connection))->getNamespaceName() . "\\Condition";
    if (!class_exists($namespace)) {
      $namespace = Condition::class;
    }
    $condition = $connection->condition('AND');
    $this->assertSame($namespace, get_class($condition));
  }

  /**
   * Tests that the method ::hasJson() returns TRUE.
   */
  public function testHasJson(): void {
    $this->assertTrue($this->connection->hasJson());
  }

  /**
   * Tests wrapping an existing connection as non-transactional.
   */
  public function testNonTransactionalWrappedConnection(): void {
    $nonTransactionalConnection = Database::getConnection(Database::DEFAULT_TARGET, Database::DEFAULT_KEY, TRUE);

    // Start a transaction on the default database, but don't commit.
    $transaction = $this->connection->startTransaction();
    $this->connection->insert('test')
      ->fields([
        'name' => 'Brad',
        'age' => 40,
        'job' => 'More Cowbell',
      ])
      ->execute();
    $cowbellPlayers = (int) $nonTransactionalConnection
      ->select('test')
      ->condition('job', 'More Cowbell')
      ->countQuery()
      ->execute()
      ->fetchField();
    if ($this->connection->allowsConcurrentNonTransactionalConnection()) {
      // The non-transactional connection doesn't see the inserted data, yet.
      $this->assertEquals(0, $cowbellPlayers);
    }
    else {
      $this->assertEquals(1, $cowbellPlayers);
    }
    $cowbellPlayers = (int) $this->connection
      ->select('test')
      ->condition('job', 'More Cowbell')
      ->countQuery()
      ->execute()
      ->fetchField();
    // The transactional connection already has more cowbell.
    // Note, this is due to the default being READ COMMITTED.
    $this->assertEquals(1, $cowbellPlayers);
    // Insert another cowbell player on the non-transactional connection.
    $nonTransactionalConnection->insert('test')
      ->fields([
        'name' => 'Gianna',
        'age' => 32,
        'job' => 'More Cowbell',
      ])
      ->execute();
    $cowbellPlayers = (int) $nonTransactionalConnection
      ->select('test')
      ->condition('job', 'More Cowbell')
      ->countQuery()
      ->execute()
      ->fetchField();
    if ($this->connection->allowsConcurrentNonTransactionalConnection()) {
      $this->assertEquals(1, $cowbellPlayers);
    }
    else {
      $this->assertEquals(2, $cowbellPlayers);
    }
    $cowbellPlayers = (int) $this->connection
      ->select('test')
      ->condition('job', 'More Cowbell')
      ->countQuery()
      ->execute()
      ->fetchField();
    if ($this->connection->allowsConcurrentNonTransactionalConnection()) {
      $this->assertEquals(1, $cowbellPlayers);
    }
    else {
      $this->assertEquals(2, $cowbellPlayers);
    }
    // Commit the transaction on destroy.
    unset($transaction);
    $cowbellPlayers = (int) $nonTransactionalConnection
      ->select('test')
      ->condition('job', 'More Cowbell')
      ->countQuery()
      ->execute()
      ->fetchField();
    // The non-transactional connection sees both players.
    $this->assertEquals(2, $cowbellPlayers);
    $cowbellPlayers = (int) $this->connection
      ->select('test')
      ->condition('job', 'More Cowbell')
      ->countQuery()
      ->execute()
      ->fetchField();
    // The transactional connection sees both players.
    $this->assertEquals(2, $cowbellPlayers);
  }

}
