<?php

declare(strict_types=1);

namespace Drupal\Tests\user\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\mysql\Driver\Database\mysql\Connection;
use Drupal\user\UserDataInterface;
use Drupal\user_data_test\JsonBackedUserData;

/**
 * Test the example JSON-backed user data service.
 *
 * This is a bit of a showcase of functionality for JSON data storage.
 *
 * @group user
 */
class JsonBackedUserDataExampleTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['user_data_test'];

  /**
   * @var \Drupal\user\UserDataInterface
   */
  protected UserDataInterface $userData;

  protected function setUp(): void {
    parent::setUp();
    if (!($this->container->get('database') instanceof Connection)) {
      $this->markTestSkipped('Only implemented for MySQL.');
    }
    $this->installSchema('user_data_test', 'users_data_json');
    $this->userData = $this->container->get(JsonBackedUserData::class);
  }

  public function testJsonBackedUserData(): void {
    $this->userData->set('user_data_test', 2, 'string_data', 'some string data');
    $this->assertEquals('some string data', $this->userData->get('user_data_test', 2)['string_data']);
    $this->assertEquals('some string data', $this->userData->get('user_data_test', 2, 'string_data'));
    $this->userData->set('user_data_test', 3, 'string_data', 'some more string data');
    $this->userData->set('user_data_test', 4, 'integer_data', 69);
    $this->assertEqualsCanonicalizing(
      [
        2 => 'some string data',
        3 => 'some more string data',
      ],
      $this->userData->get('user_data_test', name: 'string_data')
    );
    // Array data.
    $array_data = ['something' => 'else', 'another' => 'row'];
    $this->userData->set('user_data_test', 4, 'array_data', $array_data);
    $this->userData->set('user_data_test_other_module', 4, 'boolean_data', FALSE);
    $this->assertEqualsCanonicalizing(
      $array_data,
      $this->userData->get('user_data_test', 4)['array_data']
    );
    $this->assertEqualsCanonicalizing(
      [
        2 =>
          [
            'string_data' => 'some string data',
          ],
        3 =>
          [
            'string_data' => 'some more string data',
          ],
        4 =>
          [
            'array_data' =>
              [
                'another' => 'row',
                'something' => 'else',
              ],
            'integer_data' => 69,
          ],
      ],
      $this->userData->get('user_data_test')
    );
    $this->assertEqualsCanonicalizing(
      [
        4 =>
          [
            'boolean_data' => FALSE,
          ],
      ],
      $this->userData->get('user_data_test_other_module')
    );
    // Deletion is super polymorphic.
    assert($this->userData instanceof JsonBackedUserData);
    // This is not part of the interface but helps with this testing.
    $all = $this->userData->getAllForUser(4);
    $this->assertEqualsCanonicalizing(
      ['user_data_test', 'user_data_test_other_module'],
      array_keys($all)
    );
    $this->assertTrue(array_key_exists('integer_data', $all['user_data_test']));
    $this->userData->delete('user_data_test', 4, 'integer_data');
    $updated = $this->userData->getAllForUser(4);
    $this->assertFalse(array_key_exists('integer_data', $updated['user_data_test']));
    $this->userData->delete('user_data_test', 4);
    $updated = $this->userData->getAllForUser(4);
    $this->assertFalse(array_key_exists('user_data_test', $updated));
    $this->userData->delete('user_data_test');
    $this->assertEmpty($this->userData->get('user_data_test'));
  }

}
