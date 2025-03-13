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
        'prefix' => '', 
        'expectedCanonical' => 'nocase',
        'expectedMachine' => '"nocase"',
      ],
      [
        'identifier' => 'nocase', 
        'prefix' => 'foobar', 
        'expectedCanonical' => 'nocase',
        'expectedMachine' => '"foobarnocase"',
      ],
      [
        'identifier' => 'nocase', 
        'prefix' => 'VeryVeryVeryVeryVeryVeryVeryVeryLongLongLongLongLongLongLongPrefix', 
        'expectedCanonical' => 'nocase',
        'expectedMachine' => '"foobarnocase"',
      ],
      [
        'identifier' => 'camelCase', 
        'prefix' => '', 
        'expectedCanonical' => 'camelCase',
        'expectedMachine' => '"camelCase"',
      ],
      [
        'identifier' => 'camelCase', 
        'prefix' => 'foobar', 
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
        'prefix' => '', 
        'expectedCanonical' => 'brackets',
        'expectedMachine' => '"brackets"',
      ],
      [
        'identifier' => 'no/case', 
        'prefix' => '', 
        'expectedCanonical' => 'nocase',
        'expectedMachine' => '"nocase"',
      ],
      [
        'identifier' => 'no"case', 
        'prefix' => '', 
        'expectedCanonical' => 'nocase',
        'expectedMachine' => '"nocase"',
      ],
      [
        'identifier' => 'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar', 
        'prefix' => '', 
        'expectedCanonical' => 'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar',
        'expectedMachine' => '"VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar"',
      ],
      [
        'identifier' => 'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar', 
        'prefix' => 'foobar', 
        'expectedCanonical' => 'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar',
        'expectedMachine' => '"VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar"',
      ],
      [
        'identifier' => 'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar', 
        'prefix' => 'VeryVeryVeryVeryVeryVeryVeryVeryLongLongLongLongLongLongLongPrefix', 
        'expectedCanonical' => 'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar',
        'expectedMachine' => '"VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar"',
      ],
      // Sometimes, table names are following the pattern database.schema.table.
      [
        'identifier' => 'Chowra.Teressa.Bompuka.Katchal', 
        'prefix' => '', 
        'expectedCanonical' => '',
        'expectedMachine' => '',
        'expectedException' => 'The table identifier \'Chowra.Teressa.Bompuka.Katchal\' does not comply with the syntax [database.][schema.]table',
      ],
      [
        'identifier' => 'Chowra.Teressa.Bompuka', 
        'prefix' => '', 
        'expectedCanonical' => '',
        'expectedMachine' => '',
        'expectedException' => 'MySql does not support the syntax [database.][schema.]table for the table identifier \'Chowra.Teressa.Bompuka\'. Avoid specifying the \'database\' part',
      ],
      [
        'identifier' => '"Nancowry"."Tillangchong"', 
        'prefix' => '', 
        'expectedCanonical' => 'Nancowry.Tillangchong',
        'expectedMachine' => '"Nancowry"."Tillangchong"',
      ],
      [
        'identifier' => '"Nancowry"."Tillangchong"', 
        'prefix' => 'foobar', 
        'expectedCanonical' => 'Nancowry.Tillangchong',
        'expectedMachine' => '"Nancowry"."Tillangchong"',
      ],
      [
        'identifier' => '"Nancowry"."foobarTillangchong"', 
        'prefix' => 'foobar', 
        'expectedCanonical' => 'Nancowry.Tillangchong',
        'expectedMachine' => '"Nancowry"."foobarTillangchong"',
      ],
      [
        'identifier' => '!Nancowry?.$Tillangchong%%%', 
        'prefix' => '', 
        'expectedCanonical' => 'Nancowry.Tillangchong',
        'expectedMachine' => '"Nancowry"."Tillangchong"',
      ],
      [
        'identifier' => 'Camorta.VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar', 
        'prefix' => '', 
        'expectedCanonical' => '',
        'expectedMachine' => '',
        'expectedException' => '....',
      ],
      [
        'identifier' => 'VeryVeryVeryVeryHungryHungryHungryHungryHungryHungryHungryCaterpillar.Trinket', 
        'prefix' => '', 
        'expectedCanonical' => '',
        'expectedMachine' => '',
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
