<?php

declare(strict_types=1);

namespace Drupal\Tests\user\Unit;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Merge;
use Drupal\Core\Database\Query\Select;
use Drupal\Core\Database\StatementWrapperIterator;
use Drupal\Tests\UnitTestCase;
use Drupal\user\UserData;

/**
 * Test the 'user.data' service.
 *
 * @coversDefaultClass \Drupal\user\UserData
 * @group user
 */
class UserDataTest extends UnitTestCase {

  /**
   * Test the set() method.
   *
   * @param mixed $data
   *   The data to store.
   * @param string $stored_data
   *   The string expected to be stored in the database.
   * @param bool $is_serialized
   *   Is the $stored_data serialized.
   *
   * @covers ::set
   *
   * @dataProvider providerUserDataSet
   */
  public function testUserDataSet(mixed $data, string $stored_data, bool $is_serialized): void {
    $merge_mock = $this->createMock(Merge::class);
    $merge_mock->expects($this->once())
      ->method('keys')
      ->with(
        [
          'uid' => 5,
          'module' => 'unit_test',
          'name' => 'test_value',
        ],
        []
      )
      ->willReturnSelf();

    $merge_mock->expects($this->once())
      ->method('fields')
      ->with(
        [
          'value' => $stored_data,
          'serialized' => (int) $is_serialized,
        ],
        []
      )
      ->willReturnSelf();
    $merge_mock->expects($this->once())
      ->method('execute')
      ->willReturn(Merge::STATUS_INSERT);

    $connection = $this->createMock(Connection::class);
    $connection->expects($this->once())
      ->method('merge')
      ->with('users_data')
      ->willReturn($merge_mock);
    $user_data = new UserData($connection);
    $user_data->set('unit_test', 5, 'test_value', $data);
  }

  /**
   * Provider for testUserDataSet.
   */
  public function providerUserDataSet(): \Generator {

    yield 'String' => [
      'data' => 'test string',
      'stored_data' => 'test string',
      'is_serialized' => FALSE,
    ];

    yield 'Integer' => [
      'data' => 12345,
      'stored_data' => 'i:12345;',
      'is_serialized' => TRUE,
    ];

    yield 'Null' => [
      'data' => NULL,
      'stored_data' => 'N;',
      'is_serialized' => TRUE,
    ];

    yield 'True' => [
      'data' => TRUE,
      'stored_data' => 'b:1;',
      'is_serialized' => TRUE,
    ];

    yield 'False' => [
      'data' => FALSE,
      'stored_data' => 'b:0;',
      'is_serialized' => TRUE,
    ];

    yield 'Array' => [
      'data' => ['foo' => 'bar'],
      'stored_data' => 'a:1:{s:3:"foo";s:3:"bar";}',
      'is_serialized' => TRUE,
    ];

    yield 'Object' => [
      'data' => new UserDataTestObject(),
      'stored_data' => 'O:41:"Drupal\Tests\user\Unit\UserDataTestObject":0:{}',
      'is_serialized' => TRUE,
    ];
  }

  /**
   * Test the get() method.
   *
   * @param array{string, object}| \stdClass[] $return_data
   *   Data to be returned by the database.
   * @param callable $test_result
   *   Callable to test the returned result.
   * @param int|null $uid
   *   The UID to query the 'user.data' service for.
   * @param string|null $name
   *   The key name to query the 'user.data' service for.
   *
   * @covers ::get
   *
   * @dataProvider providerUserDataGet
   */
  public function testUserDataGet(array $return_data, callable $test_result, ?int $uid = NULL, ?string $name = NULL): void {
    $select_mock = $this->createMock(Select::class);
    $select_mock->expects($this->once())
      ->method('fields')
      ->with('ud')
      ->willReturnSelf();

    $called_conditions = [];
    $select_mock->method('condition')
      ->willReturnCallback(
        function () use (&$called_conditions, $select_mock) {
          $args = func_get_args();
          $called_conditions[$args[0]] = $args[1];
          return $select_mock;
        }
      );

    $result_mock = $this->createMock(StatementWrapperIterator::class);

    // Mock the iterator portion of $result_mock.
    $iterator = new \ArrayIterator($return_data);

    $result_mock
      ->method('rewind')
      ->willReturnCallback(function () use ($iterator): void {
        $iterator->rewind();
      });

    $result_mock
      ->method('current')
      ->willReturnCallback(function () use ($iterator) {
        return $iterator->current();
      });

    $result_mock
      ->method('key')
      ->willReturnCallback(function () use ($iterator) {
        return $iterator->key();
      });

    $result_mock
      ->method('next')
      ->willReturnCallback(function () use ($iterator): void {
        $iterator->next();
      });

    $result_mock
      ->method('valid')
      ->willReturnCallback(function () use ($iterator): bool {
        return $iterator->valid();
      });

    // Mock the fetchAllAssoc() portion.
    $result_mock->method('fetchAllAssoc')
      ->with('uid')
      ->willReturn($return_data);

    $select_mock->expects($this->once())
      ->method('execute')
      ->willReturn($result_mock);

    $connection = $this->createMock(Connection::class);
    $connection->expects($this->once())
      ->method('select')
      ->with('users_data', 'ud')
      ->willReturn($select_mock);
    $user_data = new UserData($connection);

    // Calling unserialize() may throw an E_NOTICE on some tests.
    error_reporting(E_ALL & ~E_NOTICE);
    $result = $user_data->get('unit_test', $uid, $name);
    $test_result($result);

    $this->assertArrayHasKey('module', $called_conditions);
    $this->assertSame('unit_test', $called_conditions['module']);

    if ($uid !== NULL) {
      $this->assertArrayHasKey('uid', $called_conditions);
      $this->assertSame($uid, $called_conditions['uid']);
    }

    if ($name !== NULL) {
      $this->assertArrayHasKey('name', $called_conditions);
      $this->assertSame($name, $called_conditions['name']);
    }

  }

  /**
   * Generator for testUserDataGet().
   */
  public function providerUserDataGet(): \Generator {

    yield 'Query UID and Name return String' => [
      'return_data' => [
        '5' => (object) [
          'serialized' => '0',
          'value' => 'test string',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame('test string', $result);
      },
      'uid' => 5,
      'name' => 'test_value',
    ];

    yield 'Query UID and Name return Integer' => [
      'return_data' => [
        '5' => (object) [
          'serialized' => '1',
          'value' => 'i:12345;',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(12345, $result);
      },
      'uid' => 5,
      'name' => 'test_value',
    ];

    yield 'Query UID and Name return Null' => [
      'return_data' => [
        '5' => (object) [
          'serialized' => '1',
          'value' => 'N;',
        ],
      ],
      'test_result' => function ($result) {
        self::assertNull($result);
      },
      'uid' => 5,
      'name' => 'test_value',
    ];

    yield 'Query UID and Name return True' => [
      'return_data' => [
        '5' => (object) [
          'serialized' => '1',
          'value' => 'b:1;',
        ],
      ],
      'test_result' => function ($result) {
        self::assertTrue($result);
      },
      'uid' => 5,
      'name' => 'test_value',
    ];

    yield 'Query UID and Name return False' => [
      'return_data' => [
        '5' => (object) [
          'serialized' => '1',
          'value' => 'b:0;',
        ],
      ],
      'test_result' => function ($result) {
        self::assertFalse($result);
      },
      'uid' => 5,
      'name' => 'test_value',
    ];

    yield 'Query UID and Name return Array' => [
      'return_data' => [
        '5' => (object) [
          'serialized' => '1',
          'value' => 'a:1:{s:3:"foo";s:3:"bar";}',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['foo' => 'bar'], $result);
      },
      'uid' => 5,
      'name' => 'test_value',
    ];

    yield 'Query UID and Name return Object' => [
      'return_data' => [
        '5' => (object) [
          'serialized' => '1',
          'value' => 'O:41:"Drupal\Tests\user\Unit\UserDataTestObject":0:{}',
        ],
      ],
      'test_result' => function ($result) {
        self::assertEquals(new UserDataTestObject(), $result);
      },
      'uid' => 5,
      'name' => 'test_value',
    ];

    yield 'Query UID and Name unknown class' => [
      'return_data' => [
        '5' => (object) [
          'serialized' => '1',
          'value' => 'O:52:"Drupal\Tests\user\Unit\NonExistentUserDataTestObject":0:{}',
        ],
      ],
      'test_result' => function ($result) {
        self::assertInstanceOf(\__PHP_Incomplete_Class::class, $result);
      },
      'uid' => 5,
      'name' => 'test_value',
    ];

    yield 'Query UID and Name unserialize() failure' => [
      'return_data' => [
        '5' => (object) [
          'serialized' => '1',
          'value' => 'O:26:"Drupal\Tests\user\Unit\UserDataTestObject":0:{}',
        ],
      ],
      'test_result' => function ($result) {
        self::assertFalse($result);
      },
      'uid' => 5,
      'name' => 'test_value',
    ];

    yield 'Query UID and Name no results' => [
      'return_data' => [],
      'test_result' => function ($result) {
        self::assertNull($result);
      },
      'uid' => 5,
      'name' => 'test_value',
    ];

    yield 'Query UID and Name string injection returns string' => [
      'return_data' => [
        '5' => (object) [
          'serialized' => '0',
          'value' => 'i:12345;',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame('i:12345;', $result);
      },
      'uid' => 5,
      'name' => 'test_value',
    ];

    yield 'Query UID return String' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'test string',
          'serialized' => '0',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['test_value' => 'test string'], $result);
      },
      'uid' => 5,
      'name' => NULL,
    ];

    yield 'Query UID return Integer' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'i:12345;',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['test_value' => 12345], $result);
      },
      'uid' => 5,
      'name' => NULL,
    ];

    yield 'Query UID return Null' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'N;',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['test_value' => NULL], $result);
      },
      'uid' => 5,
      'name' => NULL,
    ];

    yield 'Query UID return True' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'b:1;',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['test_value' => TRUE], $result);
      },
      'uid' => 5,
      'name' => NULL,
    ];

    yield 'Query UID return False' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'b:0;',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['test_value' => FALSE], $result);
      },
      'uid' => 5,
      'name' => NULL,
    ];

    yield 'Query UID return Array' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'a:1:{s:3:"foo";s:3:"bar";}',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['test_value' => ['foo' => 'bar']], $result);
      },
      'uid' => 5,
      'name' => NULL,
    ];

    yield 'Query UID return Object' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'O:41:"Drupal\Tests\user\Unit\UserDataTestObject":0:{}',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertEquals(['test_value' => new UserDataTestObject()], $result);
      },
      'uid' => 5,
      'name' => NULL,
    ];

    yield 'Query UID unknown class' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'O:52:"Drupal\Tests\user\Unit\NonExistentUserDataTestObject":0:{}',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertArrayHasKey('test_value', $result);
        self::assertInstanceOf(\__PHP_Incomplete_Class::class, $result['test_value']);
      },
      'uid' => 5,
      'name' => NULL,
    ];

    yield 'Query UID unserialize() failure' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'O:26:"Drupal\Tests\user\Unit\UserDataTestObject":0:{}',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertEquals(['test_value' => FALSE], $result);
      },
      'uid' => 5,
      'name' => NULL,
    ];

    yield 'Query UID multiple records' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'test string',
          'serialized' => '0',
        ],
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value_2',
          'value' => 'i:12345;',
          'serialized' => 1,
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(
          [
            'test_value' => 'test string',
            'test_value_2' => 12345,
          ],
          $result);
      },
      'uid' => 5,
      'name' => NULL,
    ];

    yield 'Query UID no records' => [
      'return_data' => [],
      'test_result' => function ($result) {
        self::assertEmpty($result);
      },
      'uid' => 5,
      'name' => NULL,
    ];

    yield 'Query UID string injection returns string' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'i:12345;',
          'serialized' => '0',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['test_value' => 'i:12345;'], $result);
      },
      'uid' => 5,
      'name' => NULL,
    ];

    yield 'Query Name return String' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'test string',
          'serialized' => '0',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['5' => 'test string'], $result);
      },
      'uid' => NULL,
      'name' => 'test_value',
    ];

    yield 'Query Name return Integer' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'i:12345;',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['5' => 12345], $result);
      },
      'uid' => NULL,
      'name' => 'test_value',
    ];

    yield 'Query Name return Null' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'N;',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['5' => NULL], $result);
      },
      'uid' => NULL,
      'name' => 'test_value',
    ];

    yield 'Query Name return True' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'b:1;',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['5' => TRUE], $result);
      },
      'uid' => NULL,
      'name' => 'test_value',
    ];

    yield 'Query Name return False' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'b:0;',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['5' => FALSE], $result);
      },
      'uid' => NULL,
      'name' => 'test_value',
    ];

    yield 'Query Name return Array' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'a:1:{s:3:"foo";s:3:"bar";}',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['5' => ['foo' => 'bar']], $result);
      },
      'uid' => NULL,
      'name' => 'test_value',
    ];

    yield 'Query Name return Object' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'O:41:"Drupal\Tests\user\Unit\UserDataTestObject":0:{}',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertEquals(['5' => new UserDataTestObject()], $result);
      },
      'uid' => NULL,
      'name' => 'test_value',
    ];

    yield 'Query Name unknown class' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'O:52:"Drupal\Tests\user\Unit\NonExistentUserDataTestObject":0:{}',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertArrayHasKey('5', $result);
        self::assertInstanceOf(\__PHP_Incomplete_Class::class, $result['5']);
      },
      'uid' => NULL,
      'name' => 'test_value',
    ];

    yield 'Query Name unserialize() failure' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'O:26:"Drupal\Tests\user\Unit\UserDataTestObject":0:{}',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertEquals(['5' => FALSE], $result);
      },
      'uid' => NULL,
      'name' => 'test_value',
    ];

    yield 'Query Name multiple records' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'test string',
          'serialized' => '0',
        ],
        (object) [
          'uid' => '6',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'i:12345;',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['5' => 'test string', '6' => 12345], $result);
      },
      'uid' => NULL,
      'name' => 'test_value',
    ];

    yield 'Query Name no records' => [
      'return_data' => [],
      'test_result' => function ($result) {
        self::assertEmpty($result);
      },
      'uid' => NULL,
      'name' => 'test_value',
    ];

    yield 'Query Name string injection returns string' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'i:12345;',
          'serialized' => '0',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['5' => 'i:12345;'], $result);
      },
      'uid' => NULL,
      'name' => 'test_value',
    ];

    yield 'Query module only return String' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'test string',
          'serialized' => '0',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['5' => ['test_value' => 'test string']], $result);
      },
      'uid' => NULL,
      'name' => NULL,
    ];

    yield 'Query module only return Integer' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'i:12345;',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['5' => ['test_value' => 12345]], $result);
      },
      'uid' => NULL,
      'name' => NULL,
    ];

    yield 'Query module only return Null' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'N;',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['5' => ['test_value' => NULL]], $result);
      },
      'uid' => NULL,
      'name' => NULL,
    ];

    yield 'Query module only return True' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'b:1;',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['5' => ['test_value' => TRUE]], $result);
      },
      'uid' => NULL,
      'name' => NULL,
    ];

    yield 'Query module only return False' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'b:0;',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['5' => ['test_value' => FALSE]], $result);
      },
      'uid' => NULL,
      'name' => NULL,
    ];

    yield 'Query module only return Array' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'a:1:{s:3:"foo";s:3:"bar";}',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['5' => ['test_value' => ['foo' => 'bar']]], $result);
      },
      'uid' => NULL,
      'name' => NULL,
    ];

    yield 'Query module only return Object' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'O:41:"Drupal\Tests\user\Unit\UserDataTestObject":0:{}',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertEquals(['5' => ['test_value' => new UserDataTestObject()]], $result);
      },
      'uid' => NULL,
      'name' => NULL,
    ];

    yield 'Query module only unknown class' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'O:52:"Drupal\Tests\user\Unit\NonExistentUserDataTestObject":0:{}',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertArrayHasKey('5', $result);
        self::assertArrayHasKey('test_value', $result['5']);
        self::assertInstanceOf(\__PHP_Incomplete_Class::class, $result['5']['test_value']);
      },
      'uid' => NULL,
      'name' => NULL,
    ];

    yield 'Query module only unserialize() failure' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'O:26:"Drupal\Tests\user\Unit\UserDataTestObject":0:{}',
          'serialized' => '1',
        ],
      ],
      'test_result' => function ($result) {
        self::assertEquals(['5' => ['test_value' => FALSE]], $result);
      },
      'uid' => NULL,
      'name' => NULL,
    ];

    yield 'Query module only multiple records' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'test string',
          'serialized' => '0',
        ],
        (object) [
          'uid' => '6',
          'module' => 'unit_test',
          'name' => 'test_value_2',
          'value' => 'i:12345;',
          'serialized' => 1,
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(
          [
            '5' => ['test_value' => 'test string'],
            '6' => ['test_value_2' => 12345],
          ],
          $result);
      },
      'uid' => NULL,
      'name' => NULL,
    ];

    yield 'Query module only no records' => [
      'return_data' => [],
      'test_result' => function ($result) {
        self::assertEmpty($result);
      },
      'uid' => NULL,
      'name' => NULL,
    ];

    yield 'Query module only string injection returns string' => [
      'return_data' => [
        (object) [
          'uid' => '5',
          'module' => 'unit_test',
          'name' => 'test_value',
          'value' => 'i:12345;',
          'serialized' => '0',
        ],
      ],
      'test_result' => function ($result) {
        self::assertSame(['5' => ['test_value' => 'i:12345;']], $result);
      },
      'uid' => NULL,
      'name' => NULL,
    ];

  }

}

/**
 * Object for validating deserialization.
 */
class UserDataTestObject {
}
