<?php

namespace Drupal\KernelTests\Core\Database;

use Drupal\Core\Database\Database;

/**
 * Tests the Select query builder.
 */
abstract class DriverSpecificSelectTestBase extends DriverSpecificDatabaseTestBase {

  /**
   * Tests queries with >1000 items in a IN list.
   *
   * This test is motivated by SQLite's default limit of 999 placeholders, but
   * it's good to ensure that all database drivers can handle large IN lists.
   *
   * @see https://www.sqlite.org/limits.html#max_variable_number
   */
  public function testLargeInCondition():void {
    $names = [];
    $names[] = 'John';
    for ($i = 1; $i < 500; $i++) {
      $names[] = "Name $i";
    }
    $names[] = 'George';
    for ($i = 501; $i < 1000; $i++) {
      $names[] = "Name $i";
    }
    $names[] = 'Ringo';

    // Find the above 3 Beatles.
    $num_records = $this->connection->select('test')
      ->condition('name', $names, 'IN')
      ->countQuery()
      ->execute()
      ->fetchField();
    $this->assertEquals(3, $num_records);

    // Find the other Beatle.
    $num_records = $this->connection->select('test')
      ->condition('name', $names, 'NOT IN')
      ->countQuery()
      ->execute()
      ->fetchField();
    $this->assertEquals(1, $num_records);

    // Find the above 3 Beatles with a nested condition.
    $condition = $this->connection->condition('AND');
    $condition->condition('name', $names, 'IN');
    $num_records = $this->connection->select('test')
      ->condition($condition)
      ->countQuery()
      ->execute()
      ->fetchField();
    $this->assertEquals(3, $num_records);

    // Find the other Beatle with a nested condition.
    $condition = $this->connection->condition('AND');
    $condition->condition('name', $names, 'NOT IN');
    $num_records = $this->connection->select('test')
      ->condition($condition)
      ->countQuery()
      ->execute()
      ->fetchField();
    $this->assertEquals(1, $num_records);
  }

}
