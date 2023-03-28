<?php

namespace Drupal\Tests\user\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\user\Entity\Role;

/**
 * Tests deprecated user module functions.
 *
 * @group user
 * @group legacy
 */
class LegacyUserTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
  ];

  /**
   * Tests deprecation of role grant and revoke permissions.
   *
   * @see user_role_grant_permissions()
   * @see user_role_revoke_permissions()
   */
  public function testUserPermissions() {
    $permission1 = 'administer permissions';
    $permission2 = 'administer account settings';
    $id = 'test';
    Role::create(['id' => $id, 'label' => $id])->save();

    $this->expectDeprecation('user_role_grant_permissions() is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use Drupal\user\Entity\Role::grantPermission() instead. Do not forget to call Drupal\user\Entity\Role::save() afterwards. See https://www.drupal.org/node/3348027');
    user_role_grant_permissions($id, [$permission1, $permission2]);
    $role = Role::load($id);
    $this->assertTrue($role->hasPermission($permission1));
    $this->assertTrue($role->hasPermission($permission2));
    $role->revokePermission($permission1, $permission2)->save();
    $role = Role::load($id);
    $this->assertFalse($role->hasPermission($permission1));
    $this->assertFalse($role->hasPermission($permission2));
    $role->grantPermission($permission1, $permission2)->save();
    $role = Role::load($id);
    $this->assertTrue($role->hasPermission($permission1));
    $this->assertTrue($role->hasPermission($permission2));

    $this->expectDeprecation('user_role_revoke_permissions() is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use Drupal\user\Entity\Role::revokePermission() instead. Do not forget to call Drupal\user\Entity\Role::save() afterwards. See https://www.drupal.org/node/3348027');
    user_role_revoke_permissions($id, [$permission1, $permission2]);
    $role = Role::load($id);
    $this->assertFalse($role->hasPermission($permission1));
    $this->assertFalse($role->hasPermission($permission2));
  }

}
