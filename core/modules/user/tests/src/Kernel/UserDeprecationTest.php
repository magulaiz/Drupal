<?php

namespace Drupal\Tests\user\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\EventSubscriber\UserRequestSubscriber;

/**
 * Tests deprecations in user module.
 *
 * @group user
 * @group legacy
 */
class UserDeprecationTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'user_deprecated_hooks_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
  }

  /**
   * @covers \hook_user_login
   */
  public function testHookUserLoginDeprecation(): void {
    $this->expectDeprecation('The deprecated hook hook_user_login() is implemented in these functions: user_deprecated_hooks_test_user_login(). hook_user_login() is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. In order to act on user login, subscribe to Drupal\user\Event\UserLoginEvent event. See https://www.drupal.org/node/3300476');
    user_login_finalize($this->createUser());
  }

  /**
   * @covers \Drupal\user\UserStorageInterface::updateLastAccessTimestamp
   * @covers \Drupal\user\UserStorageInterface::updateLastLoginTimestamp
   */
  public function testUserStorageMethodsDeprecation(): void {
    /** @var \Drupal\user\UserStorageInterface $storage */
    $storage = $this->container->get('entity_type.manager')->getStorage('user');
    $account = $this->createUser();
    $this->expectDeprecation("Drupal\user\UserStorage::updateLastAccessTimestamp() is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Instead, use the 'user.timestamp' service with the ::setLastAccessTime() method. See https://www.drupal.org/node/3300476");
    $storage->updateLastAccessTimestamp($account, \Drupal::time()->getRequestTime());
    $this->expectDeprecation("Drupal\user\UserStorage::updateLastLoginTimestamp() is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Instead, use the 'user.timestamp' service with the ::setLastLoginTime() method. See https://www.drupal.org/node/3300476");
    $storage->updateLastLoginTimestamp($account);
  }

  /**
   * @covers \Drupal\user\EventSubscriber\UserRequestSubscriber::__construct
   */
  public function testUserRequestSubscriberConstructorParams(): void {
    $this->expectDeprecation('The $entityTypeManager argument is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. See https://www.drupal.org/node/3300476');
    $this->expectDeprecation('Calling Drupal\user\EventSubscriber\UserRequestSubscriber::__construct() without the $userTimestamp argument is deprecated in drupal:10.1.0 and it will be required in drupal:11.0.0. See https://www.drupal.org/node/3300476');
    new UserRequestSubscriber($this->createUser(), $this->container->get('entity_type.manager'));
  }

}
