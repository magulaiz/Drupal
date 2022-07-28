<?php

namespace Drupal\Tests\user\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;

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

}
