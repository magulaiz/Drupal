<?php

namespace Drupal\KernelTests\Core\Database;

/**
 * Tests the Upsert query builder.
 */
abstract class DriverSpecificUpsertTestBase extends DriverSpecificDatabaseTestBase {

  /**
   * Confirms that we can upsert 1000 records successfully.
   *
   * This test is motivated by SQLite's default limit of 999 placeholders, but
   * it's good to ensure that all database drivers can handle large upsert
   * operations.
   *
   * @see https://www.sqlite.org/limits.html#max_variable_number
   */
  public function testLargeUpsert() {
    $num_records_before = $this->connection->query('SELECT COUNT(*) FROM {test_people}')->fetchField();

    $upsert = $this->connection->upsert('test_people')
      ->key('job')
      ->fields(['job', 'age', 'name']);

    for ($i = 0; $i < 1000; $i++) {
      $values = [
        'job' => "Job $i",
        'age' => $i,
        'name' => "Name $i",
      ];
      $upsert->values($values);
    }

    $upsert_cloned = clone $upsert;

    $upsert->execute();

    $num_records_after = $this->connection->query('SELECT COUNT(*) FROM {test_people}')->fetchField();
    $this->assertEquals($num_records_after, $num_records_before + 1000, 'Rows were inserted properly.');

    $person = $this->connection->query('SELECT * FROM {test_people} WHERE job = :job', [':job' => 'Job 0'])->fetch();
    $this->assertEquals('Job 0', $person->job, 'First job set correctly.');
    $this->assertEquals(0, $person->age, 'First age set correctly.');
    $this->assertEquals('Name 0', $person->name, 'First name set correctly.');

    $person = $this->connection->query('SELECT * FROM {test_people} WHERE job = :job', [':job' => 'Job 999'])->fetch();
    $this->assertEquals('Job 999', $person->job, 'Last job set correctly.');
    $this->assertEquals(999, $person->age, 'Last age set correctly.');
    $this->assertEquals('Name 999', $person->name, 'Last name set correctly.');
  }

}
