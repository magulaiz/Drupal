<?php

declare(strict_types=1);

namespace Drupal\Tests\mysqli\Unit;

use Drupal\mysqli\Driver\Database\mysqli\NamedPlaceholderConverter;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\mysqli\Driver\Database\mysqli\NamedPlaceholderConverter
 * @group Database
 */
class NamedPlaceholderConverterTest extends UnitTestCase {

  /**
   * @covers ::parse
   * @dataProvider statementsWithParametersProvider
   */
  public function testParse(string $sql, array $parameters, string $expectedSql, array $expectedParameters): void {
    $converter = new NamedPlaceholderConverter();
    $converter->parse($sql, $parameters);
    $this->assertSame($expectedSql, $converter->getConvertedSQL());
    $this->assertSame($expectedParameters, $converter->getConvertedParameters());
  }

  public static function statementsWithParametersProvider(): iterable {
    yield [
      'SELECT ?',
      ['foo'],
      'SELECT ?',
      ['foo'],
    ];

    yield [
      'SELECT * FROM Foo WHERE bar IN (?, ?, ?)',
      ['baz', 'qux', 'fred'],
      'SELECT * FROM Foo WHERE bar IN (?, ?, ?)',
      ['baz', 'qux', 'fred'],
    ];

    yield [
      'SELECT ? FROM ?',
      ['baz', 'qux'],
      'SELECT ? FROM ?',
      ['baz', 'qux'],
    ];

    yield [
      'SELECT "?" FROM foo WHERE bar = ?',
      ['baz'],
      'SELECT "?" FROM foo WHERE bar = ?',
      ['baz'],
    ];

    yield [
      "SELECT '?' FROM foo WHERE bar = ?",
      ['baz'],
      "SELECT '?' FROM foo WHERE bar = ?",
      ['baz'],
    ];

    yield [
      'SELECT `?` FROM foo WHERE bar = ?',
      ['baz'],
      'SELECT `?` FROM foo WHERE bar = ?',
      ['baz'],
    ];

    yield [
      'SELECT [?] FROM foo WHERE bar = ?',
      ['baz'],
      'SELECT [?] FROM foo WHERE bar = ?',
      ['baz'],
    ];

    yield [
      'SELECT * FROM foo WHERE jsonb_exists_any(foo.bar, ARRAY[?])',
      ['baz'],
      'SELECT * FROM foo WHERE jsonb_exists_any(foo.bar, ARRAY[?])',
      ['baz'],
    ];

    yield [
      "SELECT 'foo-bar?' FROM foo WHERE bar = ?",
      ['baz'],
      "SELECT 'foo-bar?' FROM foo WHERE bar = ?",
      ['baz'],
    ];

    yield [
      'SELECT "foo-bar?" FROM foo WHERE bar = ?',
      ['baz'],
      'SELECT "foo-bar?" FROM foo WHERE bar = ?',
      ['baz'],
    ];

    yield [
      'SELECT `foo-bar?` FROM foo WHERE bar = ?',
      ['baz'],
      'SELECT `foo-bar?` FROM foo WHERE bar = ?',
      ['baz'],
    ];

    yield [
      'SELECT [foo-bar?] FROM foo WHERE bar = ?',
      ['baz'],
      'SELECT [foo-bar?] FROM foo WHERE bar = ?',
      ['baz'],
    ];

    yield [
      'SELECT :foo FROM :bar',
      [':foo' => 'baz', ':bar' => 'qux'],
      'SELECT ? FROM ?',
      ['baz', 'qux'],
    ];

    yield [
      'SELECT * FROM Foo WHERE bar IN (:name1, :name2)',
      [':name1' => 'baz', ':name2' => 'qux'],
      'SELECT * FROM Foo WHERE bar IN (?, ?)',
      ['baz', 'qux'],
    ];

    yield [
      'SELECT ":foo" FROM Foo WHERE bar IN (:name1, :name2)',
      [':name1' => 'baz', ':name2' => 'qux'],
      'SELECT ":foo" FROM Foo WHERE bar IN (?, ?)',
      ['baz', 'qux'],
    ];

    yield [
      "SELECT ':foo' FROM Foo WHERE bar IN (:name1, :name2)",
      [':name1' => 'baz', ':name2' => 'qux'],
      "SELECT ':foo' FROM Foo WHERE bar IN (?, ?)",
      ['baz', 'qux'],
    ];

    yield [
      'SELECT :foo_id',
      [':foo_id' => 'bar'],
      'SELECT ?',
      ['bar'],
    ];

    yield [
      'SELECT @rank := 1 AS rank, :foo AS foo FROM :bar',
      [':foo' => 'baz', ':bar' => 'qux'],
      'SELECT @rank := 1 AS rank, ? AS foo FROM ?',
      ['baz', 'qux'],
    ];

    yield [
      'SELECT * FROM Foo WHERE bar > :start_date AND baz > :start_date',
      [':start_date' => 'qux'],
      'SELECT * FROM Foo WHERE bar > ? AND baz > ?',
      ['qux', 'qux'],
    ];

    yield [
      'SELECT foo::date as date FROM Foo WHERE bar > :start_date AND baz > :start_date',
      [':start_date' => 'qux'],
      'SELECT foo::date as date FROM Foo WHERE bar > ? AND baz > ?',
      ['qux', 'qux'],
    ];

    yield [
      'SELECT `d.ns:col_name` FROM my_table d WHERE `d.date` >= :param1',
      [':param1' => 'qux'],
      'SELECT `d.ns:col_name` FROM my_table d WHERE `d.date` >= ?',
      ['qux'],
    ];

    yield [
      'SELECT [d.ns:col_name] FROM my_table d WHERE [d.date] >= :param1',
      [':param1' => 'qux'],
      'SELECT [d.ns:col_name] FROM my_table d WHERE [d.date] >= ?',
      ['qux'],
    ];

    yield [
      'SELECT * FROM foo WHERE jsonb_exists_any(foo.bar, ARRAY[:foo])',
      'SELECT * FROM foo WHERE jsonb_exists_any(foo.bar, ARRAY[{:foo}])',
    ];

    yield [
      'SELECT * FROM foo WHERE jsonb_exists_any(foo.bar, array[:foo])',
      'SELECT * FROM foo WHERE jsonb_exists_any(foo.bar, array[{:foo}])',
    ];

    yield [
      "SELECT table.column1, ARRAY['3'] FROM schema.table table WHERE table.f1 = :foo AND ARRAY['3']",
      "SELECT table.column1, ARRAY['3'] FROM schema.table table WHERE table.f1 = {:foo} AND ARRAY['3']",
    ];

    yield [
      "SELECT table.column1, ARRAY['3']::integer[] FROM schema.table table"
        . " WHERE table.f1 = :foo AND ARRAY['3']::integer[]",
      "SELECT table.column1, ARRAY['3']::integer[] FROM schema.table table"
        . " WHERE table.f1 = {:foo} AND ARRAY['3']::integer[]",
    ];

    yield [
      "SELECT table.column1, ARRAY[:foo] FROM schema.table table WHERE table.f1 = :bar AND ARRAY['3']",
      "SELECT table.column1, ARRAY[{:foo}] FROM schema.table table WHERE table.f1 = {:bar} AND ARRAY['3']",
    ];

    yield [
      'SELECT table.column1, ARRAY[:foo]::integer[] FROM schema.table table'
        . " WHERE table.f1 = :bar AND ARRAY['3']::integer[]",
      'SELECT table.column1, ARRAY[{:foo}]::integer[] FROM schema.table table'
        . " WHERE table.f1 = {:bar} AND ARRAY['3']::integer[]",
    ];

        yield 'Quotes inside literals escaped by doubling' => [
            <<<'SQL'
SELECT * FROM foo
WHERE bar = ':not_a_param1 ''":not_a_param2"'''
   OR bar=:a_param1
   OR bar=:a_param2||':not_a_param3'
   OR bar=':not_a_param4 '':not_a_param5'' :not_a_param6'
   OR bar=''
   OR bar=:a_param3
SQL
,
            <<<'SQL'
SELECT * FROM foo
WHERE bar = ':not_a_param1 ''":not_a_param2"'''
   OR bar={:a_param1}
   OR bar={:a_param2}||':not_a_param3'
   OR bar=':not_a_param4 '':not_a_param5'' :not_a_param6'
   OR bar=''
   OR bar={:a_param3}
SQL
,
        ];

        yield [
            'SELECT data.age AS age, data.id AS id, data.name AS name, data.id AS id FROM test_data data'
                . " WHERE (data.description LIKE :condition_0 ESCAPE '\\\\')"
                . " AND (data.description LIKE :condition_1 ESCAPE '\\\\') ORDER BY id ASC",
            'SELECT data.age AS age, data.id AS id, data.name AS name, data.id AS id FROM test_data data'
                . " WHERE (data.description LIKE {:condition_0} ESCAPE '\\\\')"
                . " AND (data.description LIKE {:condition_1} ESCAPE '\\\\') ORDER BY id ASC",
        ];

        yield [
            'SELECT data.age AS age, data.id AS id, data.name AS name, data.id AS id FROM test_data data'
                . ' WHERE (data.description LIKE :condition_0 ESCAPE "\\\\")'
                . ' AND (data.description LIKE :condition_1 ESCAPE "\\\\") ORDER BY id ASC',
            'SELECT data.age AS age, data.id AS id, data.name AS name, data.id AS id FROM test_data data'
                . ' WHERE (data.description LIKE {:condition_0} ESCAPE "\\\\")'
                . ' AND (data.description LIKE {:condition_1} ESCAPE "\\\\") ORDER BY id ASC',
        ];

        yield 'Combined single and double quotes' => [
            <<<'SQL'
SELECT data.age AS age, data.id AS id, data.name AS name, data.id AS id
  FROM test_data data
 WHERE (data.description LIKE :condition_0 ESCAPE "\\")
   AND (data.description LIKE :condition_1 ESCAPE '\\') ORDER BY id ASC
SQL
,
            <<<'SQL'
SELECT data.age AS age, data.id AS id, data.name AS name, data.id AS id
  FROM test_data data
 WHERE (data.description LIKE {:condition_0} ESCAPE "\\")
   AND (data.description LIKE {:condition_1} ESCAPE '\\') ORDER BY id ASC
SQL
,
        ];

        yield [
            'SELECT data.age AS age, data.id AS id, data.name AS name, data.id AS id FROM test_data data'
                . ' WHERE (data.description LIKE :condition_0 ESCAPE `\\\\`)'
                . ' AND (data.description LIKE :condition_1 ESCAPE `\\\\`) ORDER BY id ASC',
            'SELECT data.age AS age, data.id AS id, data.name AS name, data.id AS id FROM test_data data'
                . ' WHERE (data.description LIKE {:condition_0} ESCAPE `\\\\`)'
                . ' AND (data.description LIKE {:condition_1} ESCAPE `\\\\`) ORDER BY id ASC',
        ];

        yield 'Combined single quotes and backticks' => [
            <<<'SQL'
SELECT data.age AS age, data.id AS id, data.name AS name, data.id AS id
  FROM test_data data
 WHERE (data.description LIKE :condition_0 ESCAPE '\\')
   AND (data.description LIKE :condition_1 ESCAPE `\\`) ORDER BY id ASC
SQL
,
            <<<'SQL'
SELECT data.age AS age, data.id AS id, data.name AS name, data.id AS id
  FROM test_data data
 WHERE (data.description LIKE {:condition_0} ESCAPE '\\')
   AND (data.description LIKE {:condition_1} ESCAPE `\\`) ORDER BY id ASC
SQL
,
        ];

        yield 'Placeholders inside comments' => [
            <<<'SQL'
/*
 * test placeholder ?
 */
SELECT dummy as "dummy?"
  FROM DUAL
 WHERE '?' = '?'
-- AND dummy <> ?
   AND dummy = ?
SQL
,
            <<<'SQL'
/*
 * test placeholder ?
 */
SELECT dummy as "dummy?"
  FROM DUAL
 WHERE '?' = '?'
-- AND dummy <> ?
   AND dummy = {?}
SQL
,
        ];

        yield 'Escaped question' => [
            <<<'SQL'
SELECT '{"a":null}'::jsonb ?? :key
SQL
,
            <<<'SQL'
SELECT '{"a":null}'::jsonb ?? {:key}
SQL
,
        ];
    }

}
