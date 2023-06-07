<?php

namespace Drupal\Tests\user\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\user\Entity\User;
use Drupal\user\Event\UserAuthenticationEvent;
use Drupal\user\Event\UserEvents;

/**
 * Tests for user authentication event subscriber.
 *
 * @group user
 */
class UserAuthenticationEventSubscriberTest extends EntityKernelTestBase {

  /**
   * The name of the field to use for testing.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * The default value for the testing.
   *
   * @var string
   */
  protected $defaultValue;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['user', 'system', 'field', 'user_events_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig([
      'system',
      'field',
    ]);

    // Name of the test field.
    /* @see \Drupal\user_events_test\EventSubscriber\UserAuthenticationSubscriber::onUserLogin */
    /* @see \Drupal\user_events_test\EventSubscriber\UserAuthenticationSubscriber::onUserLogout() */
    $this->fieldName = 'test_field';
    // Default value.
    $this->defaultValue = 'test_value';

    // Create field for testing value.
    FieldStorageConfig::create([
      'field_name' => $this->fieldName,
      'entity_type' => 'user',
      'type' => 'string',
    ])->save();

    FieldConfig::create([
      'entity_type' => 'user',
      'field_name' => $this->fieldName,
      'bundle' => 'user',
    ])->save();

  }

  /**
   * Tests account's available fields.
   */
  public function testUserLoginEventSubscriber() {
    // Create the user to test.
    $user = User::create([
      'name' => 'foobar',
      $this->fieldName => $this->defaultValue,
    ]);

    $this->assertEquals($this->defaultValue, $user->{$this->fieldName}->value);
    // Call the user login event.
    $event = new UserAuthenticationEvent($user);
    \Drupal::service('event_dispatcher')->dispatch($event, UserEvents::USER_LOGIN);
    $this->assertEquals('subscriber_event_triggered', $user->{$this->fieldName}->value);
  }

  /**
   * Tests account's available fields.
   */
  public function testUserLogoutEventSubscriber() {
    // Create the user to test.
    $user = User::create([
      'name' => 'foobar',
      $this->fieldName => $this->defaultValue,
    ]);

    $this->assertEquals($this->defaultValue, $user->{$this->fieldName}->value);
    // Call the user logout event.
    $event = new UserAuthenticationEvent($user);
    \Drupal::service('event_dispatcher')->dispatch($event, UserEvents::USER_LOGOUT);
    $this->assertEquals('subscriber_event_triggered', $user->{$this->fieldName}->value);
  }

}
