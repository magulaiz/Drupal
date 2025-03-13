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
   * Data provider for testTable.
   *
   * @return array
   *   An indexed array of where each value is an array of arguments to pass to
   *   testEscapeField. The first value is the expected value, and the second
   *   value is the value to test.
   */
  public static function providerTable(): array {
    return [
      [
        'identifier' => 'nocase', 
        'expectedCanonical' => 'nocase',
        'expectedMachine' => '"nocase"',
      ],
      [
        'identifier' => 'camelCase', 
        'expectedCanonical' => 'camelCase',
        'expectedMachine' => '"camelCase"',
      ],
      [
        'identifier' => '`backtick`', 
        'expectedCanonical' => 'backtick',
        'expectedMachine' => '"backtick"',
      ],
      [
        'identifier' => '[brackets]', 
        'expectedCanonical' => 'brackets',
        'expectedMachine' => '"brackets"',
      ],
      [
        'identifier' => 'no/case', 
        'expectedCanonical' => 'nocase',
        'expectedMachine' => '"nocase"',
      ],
      [
        'identifier' => 'no"case', 
        'expectedCanonical' => 'nocase',
        'expectedMachine' => '"nocase"',
      ],
      [
        'identifier' => 'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar', 
        'expectedCanonical' => 'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar',
        'expectedMachine' => '"VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar"',
      ],
      // Sometimes, table names are following the pattern database.schema.table.
      [
        'identifier' => 'Chowra.Teressa.Bompuka.Katchal', 
      ],
      [
        'identifier' => 'Chowra.Teressa.Bompuka', 
      ],
      [
        'identifier' => '"Nancowry"."Tillangchong"', 
        'expectedCanonical' => 'Nancowry.Tillangchong',
        'expectedMachine' => '"Nancowry"."Tillangchong"',
      ],
      [
        'identifier' => '!Nancowry?.$Tillangchong%%%', 
        'expectedCanonical' => 'Nancowry.Tillangchong',
        'expectedMachine' => '"Nancowry"."Tillangchong"',
      ],
      [
        'identifier' => 'Camorta.VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar', 
      ],
      [
        'identifier' => 'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar.Trinket', 
      ],
    ];
  }

  /**
   * @dataProvider providerTable
   */
  public function testTable(string $identifier, ?string $expectedCanonical = '', ?string $expectedMachine = '', ?string $expectedException = NULL): void {
    $connection = new Connection($this->createMock(\PDO::class), [
      'prefix' => 'blahblah',
      'init_commands' => [
        'sql_mode' => 'ANSI',
      ],
    ]);
    $this->assertSame($expectedCanonical, $connection->identifiers->table($identifier)->canonical());
    $this->assertSame($expectedMachine, $connection->identifiers->table($identifier)->forMachine());
  }

}
