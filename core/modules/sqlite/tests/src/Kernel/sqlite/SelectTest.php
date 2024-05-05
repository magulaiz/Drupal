<?php

namespace Drupal\Tests\sqlite\Kernel\sqlite;

use Drupal\KernelTests\Core\Database\DriverSpecificSelectTestBase;

/**
 * Tests the Select query builder for SQLite.
 *
 * @group Database
 */
class SelectTest extends DriverSpecificSelectTestBase {

  /**
   * {@inheritdoc}
   */
  public function testLargeInCondition(): void {
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

    // The problem with testing that we want to use more variables then the
    // maximum limit that SQLite allows. The problem is that we do not know what
    // that maximum is. SQLite can be compiled with a setting that is much
    // higher then the default value of 999. What we can test is that when we
    // use a large number of variables in a query, that SQLite places them in a
    // single placeholder.
    $select = (string) $this->connection->select('test')
      ->condition('name', $names, 'IN');
    $this->assertEquals(1, substr_count($select, ':db_condition_placeholder'), 'There should only be one condition placeholder used in the query for SQLite.');

    $select = (string) $this->connection->select('test')
      ->condition('name', $names, 'NOT IN');
    $this->assertEquals(1, substr_count($select, ':db_condition_placeholder'), 'There should only be one condition placeholder used in the query for SQLite.');

    $condition = $this->connection->condition('AND');
    $condition->condition('name', $names, 'IN');
    $select = (string) $this->connection->select('test')
      ->condition($condition);
    $this->assertEquals(1, substr_count($select, ':db_condition_placeholder'), 'There should only be one condition placeholder used in the query for SQLite.');

    $condition = $this->connection->condition('AND');
    $condition->condition('name', $names, 'NOT IN');
    $select = (string) $this->connection->select('test')
      ->condition($condition);
    $this->assertEquals(1, substr_count($select, ':db_condition_placeholder'), 'There should only be one condition placeholder used in the query for SQLite.');
  }

}
