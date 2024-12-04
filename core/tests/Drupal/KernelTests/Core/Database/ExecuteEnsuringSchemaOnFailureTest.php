<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Database;

use Drupal\Core\Database\DatabaseException;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\Database\Exception\SchemaObjectCreationFailureException;

/**
 * Tests Connection::executeEnsuringSchemaOnFailure().
 *
 * @group Database
 */
class ExecuteEnsuringSchemaOnFailureTest extends DatabaseTestBase {

  public function testValidCallbackOnExistingSchema(): void {
    // Create the tables before executing the test.
    $this->connection->schema()->createTable('fixture_header', $this->validMultiTableFixtureSchema()['fixture_header']);
    $this->connection->schema()->createTable('fixture_detail', $this->validMultiTableFixtureSchema()['fixture_detail']);

    $execution = $this->connection->executeEnsuringSchemaOnFailure(
      execute: function (): int {
        return $this->insertIntoFixtureTables();
      },
      schema: $this->validMultiTableFixtureSchema(),
      retryAfterSchemaEnsured: TRUE,
    );

    // The schema was there so first callback execution was successful.
    $this->assertTrue($execution->isSuccessful());
    $this->assertSame(1, $execution->getResult());
    $this->assertEquals(1, $this->connection->select('fixture_header', 'h')->countQuery()->execute()->fetchField());
    $this->assertEquals(1, $this->connection->select('fixture_detail', 'h')->countQuery()->execute()->fetchField());
  }

  public function testValidCallbackOnMissingMultiTableSchema(): void {
    $execution = $this->connection->executeEnsuringSchemaOnFailure(
      execute: function (): int {
        return $this->insertIntoFixtureTables();
      },
      schema: $this->validMultiTableFixtureSchema(),
      retryAfterSchemaEnsured: TRUE,
    );

    // The initial callback execution failed, but the schema creation was
    // successful and the callback retry was successful too.
    $this->assertTrue($execution->isSuccessful());
    $this->assertSame(1, $execution->getResult());
    $this->assertEquals(1, $this->connection->select('fixture_header', 'h')->countQuery()->execute()->fetchField());
    $this->assertEquals(1, $this->connection->select('fixture_detail', 'h')->countQuery()->execute()->fetchField());
  }

  public function testInvalidCallbackOnExistingSchema(): void {
    $this->connection->schema()->createTable('fixture_header', $this->validMultiTableFixtureSchema()['fixture_header']);
    $this->connection->schema()->createTable('fixture_detail', $this->validMultiTableFixtureSchema()['fixture_detail']);

    // The schema is there already, so callback execution just throws an
    // exception, which is propagated through to the caller of
    // ::executeEnsuringSchemaOnFailure().
    $this->expectException(DatabaseExceptionWrapper::class);

    $this->connection->executeEnsuringSchemaOnFailure(
      execute: function (): int {
        return $this->connection->query('bananas')->execute()->fetchField();
      },
      schema: $this->validMultiTableFixtureSchema(),
    );
  }

  public function testInvalidCallbackOnMissingMultiTableSchema(): void {
    $this->assertFalse($this->connection->schema()->tableExists('fixture_header'));
    $this->assertFalse($this->connection->schema()->tableExists('fixture_detail'));

    try {
      $execution = $this->connection->executeEnsuringSchemaOnFailure(
        execute: function (): int {
          return $this->connection->query('bananas')->execute()->fetchField();
        },
        schema: $this->validMultiTableFixtureSchema(),
      );
      $this->fail('executeEnsuringSchemaOnFailure() should have thrown an exception, but it did not.');
    }
    catch (\Exception $e) {
      $this->assertInstanceOf(DatabaseException::class, $e);
    }

    // The initial and retried callback execution failed, but the schema
    // creation was successful.
    $this->assertTrue($this->connection->schema()->tableExists('fixture_header'));
    $this->assertTrue($this->connection->schema()->tableExists('fixture_detail'));
  }

  public function testValidCallbackOnMissingInvalidSchema(): void {
    // The initial callback execution failed, and the schema creation was
    // unsuccessful too. We expect an exception reporting the failed schema
    // operation.
    $this->expectException(SchemaObjectCreationFailureException::class);
    $this->expectExceptionMessage("Failed creation of table {pineapple}");

    $this->connection->executeEnsuringSchemaOnFailure(
      execute: function (): int {
        return $this->insertIntoFixtureTables();
      },
      schema: $this->invalidFixtureSchema(),
      retryAfterSchemaEnsured: TRUE,
    );
  }

  protected function validMultiTableFixtureSchema(): array {
    return [
      'fixture_header' => [
        'fields' => [
          'hid' => [
            'type' => 'serial',
            'not null' => TRUE,
            'description' => 'Primary Key: An ID.',
          ],
          'message' => [
            'type' => 'text',
            'not null' => TRUE,
            'description' => 'A message for the header.',
          ],
        ],
        'primary key' => ['hid'],
      ],
      'fixture_detail' => [
        'fields' => [
          'hid' => [
            'type' => 'int',
            'not null' => TRUE,
            'description' => 'The header ID.',
          ],
          'did' => [
            'type' => 'serial',
            'not null' => TRUE,
            'description' => 'Primary Key: An ID.',
          ],
          'message' => [
            'type' => 'text',
            'not null' => TRUE,
            'description' => 'A message for the detail.',
          ],
        ],
        'primary key' => ['did'],
        'indexes' => [
          'hid_did' => ['hid', 'did'],
        ],
      ],
    ];
  }

  protected function insertIntoFixtureTables(): int {
    $hid = (int) $this->connection->insert('fixture_header')
      ->fields([
        'message' => 'foo',
      ])
      ->execute();

    $this->connection->insert('fixture_detail')
      ->fields([
        'hid' => $hid,
        'message' => 'bar',
      ])
      ->execute();

    return $hid;
  }

  protected function invalidFixtureSchema(): array {
    return [
      'pineapple' => [
        'description' => 'under the sea',
        'fields' => [],
      ],
    ];
  }

}
