<?php

namespace Drupal\Tests\user\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drupal\user\Entity\User;

/**
 * Update path tests for user module.
 *
 * @group user
 */
class UserUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles(): void {
    $this->databaseDumpFiles[] = __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.3.0.bare.standard.php.gz';
  }

  /**
   * @covers \user_update_10100
   */
  public function testUserLoginAndAccessUpdate(): void {
    $fields = [
      'access' => 'getLastAccessedTime',
      'login' => 'getLastLoginTime',
    ];

    $db = \Drupal::database();
    $schema = $db->schema();

    // Storage is in place before update.
    foreach ($fields as $field => $getter) {
      $this->assertTrue($schema->fieldExists('users_field_data', $field));
    }

    // Get user data before updates.
    $data = $db->select('users_field_data')
      ->fields('users_field_data', array_keys($fields))
      ->condition('uid', 1)
      ->execute()
      ->fetch();

    $this->runUpdates();

    $definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions('user', 'user');
    foreach ($fields as $field => $getter) {
      // Check field definitions update.
      $class = 'Drupal\user\UserLast' . ucfirst($field) . 'FieldItemList';
      $this->assertTrue($definitions[$field]->isComputed());
      $this->assertSame($class, $definitions[$field]->getClass());

      // Check that table columns were removed.
      $this->assertFalse($schema->fieldExists('users_field_data', $field));

      // Check that values were ported to the key/value store.
      $this->assertSame($data->{$field}, \Drupal::keyValue("user.timestamp.$field")->get(1));

      // Check using entity API.
      $this->assertSame($data->{$field}, User::load(1)->{$getter}());
    }
  }

}
