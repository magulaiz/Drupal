<?php

namespace Drupal\Tests\user\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Permission handler tests.
 *
 * @group user
 */
class PermissionHandlerTest extends KernelTestBase {

  protected static $modules = ['system', 'user', 'user_permissions_test', 'user_filtered_permissions_test'];

  public function testFilteredPermissionsHook() {
    $permission_handler = $this->container->get('user.permissions');
    $permissions = $permission_handler->getPermissions();
    $filtered_permissions = $permission_handler->getFilteredPermissions();
    $this->assertGreaterThan(3, count($permissions));
    $filtered_permission_names = array_keys($filtered_permissions);
    sort($filtered_permission_names);
    $this->assertSame(['a', 'b', 'c'], $filtered_permission_names);
  }

}
