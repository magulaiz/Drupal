<?php

declare(strict_types=1);

namespace Drupal\Tests\mysql\Unit;

use Drupal\mysql\Driver\Database\mysql\Connection;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;

/**
 * Tests MySQL database identifiers.
 *
 * @coversDefaultClass \Drupal\mysql\Driver\Database\mysql\IdentifierHandler
 * @group Database
 */
class IdentifierTest extends UnitTestCase {

  /**
   * A MySql connection.
   */
  private Connection $connection;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->connection = new Connection($this->createMock(\PDO::class), [
      'prefix' => 'blahblah',
      'init_commands' => [
        'sql_mode' => 'ANSI',
      ],
    ]);
  }

  /**
   * Data provider for testTable.
   *
   * @return array
   *   An indexed array of where each value is an array of arguments to pass to
   *   testEscapeField. The first value is the expected value, and the second
   *   value is the value to test.
   */
  public static function providerTable(): array {
    return [
      ['nocase', 'nocase'],
      ['camelCase', 'camelCase'],
      ['backtick', '`backtick`', ['`', '`']],
      ['brackets', '[brackets]', ['[', ']']],
      ['camelCase', '"camelCase"'],
      ['camelCase', 'camel/Case'],
      ['camelCase', 'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar'],
      // Sometimes, table names are following the pattern database.schema.table.
      ['', 'Chowra.Teressa.Bompuka.Katchal'],
      ['', 'Chowra.Teressa.Bompuka'],
      ['"Nancowry"."Tillangchong"', '"Nancowry"."Tillangchong"'],
      ['"Nancowry"."Tillangchong"', '!Nancowry?.$Tillangchong%%%'],
      ['', 'Camorta.VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar'],
      ['', 'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar.Trinket'],
    ];
  }

  /**
   * @dataProvider providerTable
   */
  public function testTable($expected, $name, array $identifier_quote = ['"', '"']): void {
    $this->assertEquals($expected, $this->connection->identifiers->table($name)->forMachine());
  }

}
