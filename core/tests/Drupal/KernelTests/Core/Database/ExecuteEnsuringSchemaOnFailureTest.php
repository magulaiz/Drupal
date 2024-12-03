<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Database;

use Drupal\Core\Database\DatabaseExceptionWrapper;

/**
 * Tests Connection::executeEnsuringSchemaOnFailure().
 *
 * @group Database
 */
class ExecuteEnsuringSchemaOnFailureTest extends DatabaseTestBase {

  public function testInvalidCallbackOnMissingMultiTableSchema(): void {
    $this->assertFalse($this->connection->schema()->tableExists('fixture_header'));
    $this->assertFalse($this->connection->schema()->tableExists('fixture_detail'));

    $callbackSuccess = $this->connection->executeEnsuringSchemaOnFailure(
      execute: function (): int {
        return $this->connection->query('bananas')->execute()->fetchField();
      },
      returnValue: $id,
      schema: $this->validMultiTableSchemaFixture(),
    );

    $this->assertFalse($callbackSuccess);
    $this->assertNull($id);
    $this->assertTrue($this->connection->schema()->tableExists('fixture_header'));
    $this->assertTrue($this->connection->schema()->tableExists('fixture_detail'));
  }

  public function testInvalidCallbackOnExistingMultiTableSchema(): void {
    $this->connection->schema()->createTable('fixture_header', $this->validMultiTableSchemaFixture()['fixture_header']);
    $this->connection->schema()->createTable('fixture_detail', $this->validMultiTableSchemaFixture()['fixture_detail']);

    $this->expectException(DatabaseExceptionWrapper::class);

    $this->connection->executeEnsuringSchemaOnFailure(
      execute: function (): int {
        return $this->connection->query('bananas')->execute()->fetchField();
      },
      returnValue: $id,
      schema: $this->validMultiTableSchemaFixture(),
    );
  }

  public function testCallbackFailureAndMultiTableSchemaBuilt(): void {
    $this->assertFalse($this->connection->schema()->tableExists('fixture_header'));
    $this->assertFalse($this->connection->schema()->tableExists('fixture_detail'));

    $this->connection->executeEnsuringSchemaOnFailure(
      execute: function (): int {
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
      },
      returnValue: $id,
      schema: $this->validMultiTableSchemaFixture(),
      retryAfterSchemaEnsured: TRUE,
    );

    $this->assertSame(1, $id);
    $this->assertEquals(1, $this->connection->select('fixture_header', 'h')->countQuery()->execute()->fetchField());
    $this->assertEquals(1, $this->connection->select('fixture_detail', 'h')->countQuery()->execute()->fetchField());
  }

  protected function validMultiTableSchemaFixture(): array {
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
            'hdid' => ['hid', 'did'],
        ],
      ],
    ];
  }

}
