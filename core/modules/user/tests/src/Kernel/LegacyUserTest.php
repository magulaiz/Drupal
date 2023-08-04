<?php

namespace Drupal\Tests\user\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests deprecated user module functions.
 *
 * @group user
 * @group legacy
 */
class LegacyUserTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
  }

  /**
   * Tests deprecation of user_role_permissions().
   */
  public function testUserRolePermissions() {
    $this->expectDeprecation('user_role_permissions() is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. There is no replacement beyond loading the roles and calling \Drupal\user\Entity\Role::getPermissions(). See https://www.drupal.org/node/3348138');

    $expected = [
      RoleInterface::ANONYMOUS_ID => [],
      RoleInterface::AUTHENTICATED_ID => [],
    ];
    $permissions = user_role_permissions(array_keys($expected));
    $this->assertSame($expected, $permissions);

    $permission = 'administer permissions';
    $role = Role::create([
      'id' => 'admin',
      'label' => 'Test',
      'is_admin' => TRUE,
      'permissions' => [$permission],
    ]);
    $role->save();
    $permissions = user_role_permissions([$role->id()]);
    $this->assertSame([$role->id() => []], $permissions);
    $role
      ->setIsAdmin(FALSE)
      ->grantPermission($permission)
      ->save();
    $permissions = user_role_permissions([$role->id()]);
    $this->assertSame([$role->id() => [$permission]], $permissions);
  }

  /**
   * Tests the user_login_finalize() deprecation.
   */
  public function testUserLoginDeprecation(): void {
    $this->expectDeprecation("user_login_finalize() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use Drupal\user\UserSessionHandlerInterface::login() instead. See https://www.drupal.org/node/3379194");
    $account = $this->createUser();
    user_login_finalize($account);
  }

  /**
   * Tests the user_logout() deprecation.
   */
  public function testUserLogoutDeprecation(): void {
    $this->expectDeprecation("user_logout() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use Drupal\user\UserSessionHandlerInterface::logout() instead. See https://www.drupal.org/node/3379194");
    user_logout();
  }

}
