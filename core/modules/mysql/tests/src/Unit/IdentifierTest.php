<?php

declare(strict_types=1);

namespace Drupal\Tests\mysql\Unit;

use Drupal\Core\Database\Exception\IdentifierException;
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
        'prefix' => 'foobar', 
        'identifier' => 'nocase', 
        'expectedCanonical' => 'nocase',
        'expectedMachine' => '"foobarnocase"',
      ],
      [
        'prefix' => 'VeryVeryVeryVeryVeryVeryVeryVeryLongLongLongLongLongLongLongPrefix', 
        'identifier' => 'nocase', 
        'expectedCanonical' => 'nocase',
        'expectedMachine' => '"foobarnocase"',
      ],
      [
        'identifier' => 'camelCase', 
        'expectedCanonical' => 'camelCase',
        'expectedMachine' => '"camelCase"',
      ],
      [
        'prefix' => 'foobar', 
        'identifier' => 'camelCase', 
        'expectedCanonical' => 'camelCase',
        'expectedMachine' => '"foobarcamelCase"',
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
      [
        'prefix' => 'foobar', 
        'identifier' => 'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar', 
        'expectedCanonical' => 'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar',
        'expectedMachine' => '"VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar"',
      ],
      [
        'prefix' => 'VeryVeryVeryVeryVeryVeryVeryVeryLongLongLongLongLongLongLongPrefix', 
        'identifier' => 'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar', 
        'expectedCanonical' => 'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar',
        'expectedMachine' => '"VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar"',
      ],
      // Sometimes, table names are following the pattern database.schema.table.
      [
        'identifier' => 'Chowra.Teressa.Bompuka.Katchal', 
        'expectedException' => 'The table identifier \'Chowra.Teressa.Bompuka.Katchal\' does not comply with the syntax [database.][schema.]table',
      ],
      [
        'identifier' => 'Chowra.Teressa.Bompuka', 
        'expectedException' => 'MySql does not support the syntax [database.][schema.]table for the table identifier \'Chowra.Teressa.Bompuka\'. Avoid specifying the \'database\' part',
      ],
      [
        'identifier' => '"Nancowry"."Tillangchong"', 
        'expectedCanonical' => 'Nancowry.Tillangchong',
        'expectedMachine' => '"Nancowry"."Tillangchong"',
      ],
      [
        'prefix' => 'foobar', 
        'identifier' => '"Nancowry"."Tillangchong"', 
        'expectedCanonical' => 'Nancowry.Tillangchong',
        'expectedMachine' => '"Nancowry"."Tillangchong"',
      ],
      [
        'prefix' => 'foobar', 
        'identifier' => '"Nancowry"."foobarTillangchong"', 
        'expectedCanonical' => 'Nancowry.Tillangchong',
        'expectedMachine' => '"Nancowry"."foobarTillangchong"',
      ],
      [
        'identifier' => '!Nancowry?.$Tillangchong%%%', 
        'expectedCanonical' => 'Nancowry.Tillangchong',
        'expectedMachine' => '"Nancowry"."Tillangchong"',
      ],
      [
        'identifier' => 'Camorta.VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar', 
        'expectedException' => '....',
      ],
      [
        'identifier' => 'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar.Trinket', 
        'expectedException' => 'The length of the schema identifier \'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar\' once canonicalized to \'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar\' is invalid (maximum allowed: 64)',
      ],
    ];
  }

  /**
   * @dataProvider providerTable
   */
  public function testTable(string $identifier, string $prefix = '', ?string $expectedCanonical = '', ?string $expectedMachine = '', ?string $expectedException = NULL): void {
    $connection = new Connection($this->createMock(\PDO::class), [
      'prefix' => $prefix,
      'init_commands' => [
        'sql_mode' => 'ANSI',
      ],
    ]);
    if ($expectedException) {
      $this->expectException(IdentifierException::class);
      $this->expectExceptionMessage($expectedException);
    }
    $this->assertSame($expectedCanonical, $connection->identifiers->table($identifier)->canonical());
    $this->assertSame($expectedMachine, $connection->identifiers->table($identifier)->forMachine());
  }

}
