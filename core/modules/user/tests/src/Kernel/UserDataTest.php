<?php

declare(strict_types=1);

namespace Drupal\Tests\user\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Test the 'user.data' service.
 *
 * @coversDefaultClass \Drupal\user\UserData
 * @group user
 */
class UserDataTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->installSchema('user', ['users_data']);
  }

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
    /** @var \Drupal\user\UserDataInterface $user_data */
    $user_data = \Drupal::service('user.data');
    $user_data->set('user', 5, 'testUserDataSetValue', $data);
    $connection = \Drupal::database();
    $query = $connection->select('users_data', 'u')
      ->condition('u.name', 'testUserDataSetValue', '=')
      ->condition('u.module', 'user', '=')
      ->condition('u.uid', '5', '=')
      ->fields('u', ['value', 'serialized']);
    /** @var \stdClass{'value': string|int, 'serialized', string|int}|false $result */
    $result = $query->execute()->fetch();
    $this->assertNotFalse($result);
    $this->assertSame($stored_data, $result->value);
    $this->assertEquals($is_serialized, $result->serialized);
  }

  /**
   * Provider for testUserDataSet.
   */
  public static function providerUserDataSet(): \Generator {

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
      'stored_data' => 'O:43:"Drupal\Tests\user\Kernel\UserDataTestObject":0:{}',
      'is_serialized' => TRUE,
    ];
  }

  /**
   * Test the get() method.
   *
   * @param string $stored_data
   *   Data to be returned by the database.
   * @param bool $is_serialized
   *   Is the $stored_data a serialized string.
   * @param mixed $expected_result
   *   Value expected to be returned by get().
   *
   * @covers ::get
   *
   * @dataProvider providerUserDataGet
   */
  public function testUserDataGet(string $stored_data, bool $is_serialized, mixed $expected_result): void {
    $connection = \Drupal::database();
    $connection
      ->insert('users_data')
      ->fields(
        [
          'uid' => '5',
          'module' => 'user',
          'name' => 'testUserDataSetValue',
          'value' => $stored_data,
          'serialized' => (int) $is_serialized,
        ]
      )
      ->execute();
    // Calling unserialize() may throw an E_NOTICE on some tests.
    error_reporting(E_ALL & ~E_NOTICE);
    /** @var \Drupal\user\UserDataInterface $user_data */
    $user_data = \Drupal::service('user.data');
    $result = $user_data->get('user', 5, 'testUserDataSetValue');
    if (!is_object($expected_result)) {
      $this->assertSame($expected_result, $result);
    }
    else {
      $this->assertEquals($expected_result, $result);
    }

    $interim_result = $user_data->get('user', 5);
    $result = $interim_result['testUserDataSetValue'];
    if (!is_object($expected_result)) {
      $this->assertSame($expected_result, $result);
    }
    else {
      $this->assertEquals($expected_result, $result);
    }

    $interim_result = $user_data->get('user', NULL, 'testUserDataSetValue');
    $result = $interim_result['5'];
    if (!is_object($expected_result)) {
      $this->assertSame($expected_result, $result);
    }
    else {
      $this->assertEquals($expected_result, $result);
    }

  }

  /**
   * Generator for testUserDataGet().
   */
  public static function providerUserDataGet(): \Generator {

    yield 'String' => [
      'stored_data' => 'test string',
      'is_serialized' => FALSE,
      'test_result' => 'test string',
    ];

    yield 'Integer' => [
      'stored_data' => 'i:12345;',
      'is_serialized' => TRUE,
      'test_result' => 12345,
    ];

    yield 'Null' => [
      'value' => 'N;',
      'is_serialized' => TRUE,
      'test_result' => NULL,
    ];

    yield 'True' => [
      'value' => 'b:1;',
      'is_serialized' => TRUE,
      'test_result' => TRUE,

    ];

    yield 'False' => [
      'value' => 'b:0;',
      'is_serialized' => TRUE,
      'test_result' => FALSE,
    ];

    yield 'Array' => [
      'value' => 'a:1:{s:3:"foo";s:3:"bar";}',
      'is_serialized' => TRUE,
      'test_result' => ['foo' => 'bar'],

    ];

    yield 'Object' => [
      'value' => 'O:43:"Drupal\Tests\user\Kernel\UserDataTestObject":0:{}',
      'is_serialized' => TRUE,
      'test_result' => new UserDataTestObject(),
    ];

    yield 'Unknown class' => [
      'value' => 'O:52:"Drupal\Tests\user\Kernel\NonExistentUserDataTestObject":0:{}',
      'is_serialized' => TRUE,
      'test_result' => @unserialize('O:52:"Drupal\Tests\user\Kernel\NonExistentUserDataTestObject":0:{}'),
      'uid' => 5,
      'name' => 'test_value',
    ];

    yield 'Failure in unserialize()' => [
      'value' => 'O:26:"Drupal\Tests\user\Kernel\UserDataTestObject":0:{}',
      'is_serialized' => TRUE,
      'test_result' => FALSE,
    ];

    yield 'Serialization injection returns string' => [
      'value' => 'i:12345;',
      'is_serialized' => FALSE,
      'test_result' => 'i:12345;',
    ];

    yield 'Integer like string' => [
      'stored_data' => '12345',
      'is_serialized' => FALSE,
      'test_result' => '12345',
    ];

  }

  /**
   * Test when no results match.
   */
  public function testUserDataGetNoResults(): void {
    /** @var \Drupal\user\UserDataInterface $user_data */
    $user_data = \Drupal::service('user.data');
    $result = $user_data->get('user', 5, 'testUserDataSetValue');
    $this->assertNull($result);
    $result = $user_data->get('user', 5);
    $this->assertEmpty($result);
    $result = $user_data->get('user', NULL, 'testUserDataSetValue');
    $this->assertEmpty($result);
  }

}

/**
 * Object for validating deserialization.
 */
class UserDataTestObject {
}
