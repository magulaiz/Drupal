<?php

namespace Drupal\Tests\sqlite\Kernel\sqlite;

use Drupal\KernelTests\Core\Database\DriverSpecificUpsertTestBase;
use Drupal\sqlite\Driver\Database\sqlite\Connection;

/**
 * Tests the Upsert query builder for SQLite.
 *
 * @group Database
 */
class UpsertTest extends DriverSpecificUpsertTestBase {

  /**
   * {@inheritdoc}
   */
  public function testLargeUpsert(): void {
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

    // The problem with testing that we want to use more variables then the
    // maximum limit that SQLite allows. The problem is that we do not know what
    // that maximum is. SQLite can be compiled with a setting that is much
    // higher then the default value of 999. What we can test is that when we
    // use a large number of variables in a query, that SQLite places them in a
    // single placeholder.
    $connection_mock = $this->getMockBuilder(Connection::class)
      ->disableOriginalConstructor()
      ->setMethods(['query', 'identifierQuote'])
      ->getMockForAbstractClass();

    // Mock the method query, so that is return the query string instead of
    // execution the query.
    $connection_mock->expects($this->once())
      ->method('query')
      ->willReturnArgument(0);

    // Mock the method identifierQuote, so that no deprecation is triggered.
    $connection_mock->expects($this->any())
      ->method('identifierQuote')
      ->willReturn('"');

    $property_ref = new \ReflectionProperty($upsert_cloned, 'connection');
    $property_ref->setValue($upsert_cloned, $connection_mock);

    $this->assertEquals(1, substr_count($upsert_cloned->execute(), ':db_insert_placeholder'), 'There should only be one placeholder used in the query for SQLite.');
  }

}
