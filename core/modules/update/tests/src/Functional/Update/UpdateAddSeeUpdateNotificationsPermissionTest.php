<?php

namespace Drupal\Tests\update\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\Role;

/**
 * Tests update_post_update_add_see_update_notifications_permission().
 *
 * @group Update
 * @group legacy
 */
class UpdateAddSeeUpdateNotificationsPermissionTest extends UpdatePathTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles(): void {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.3.0.filled.standard.php.gz',
    ];
  }

  /**
   * Tests that the 'see update notifications' permission is correctly granted.
   */
  public function testSeeUpdateNotificationsPermission(): void {
    // Add a new 'Junior Admin' role with the legacy permission we care about.
    $junior_admin = $this->createRole(
      ['administer site configuration'],
      'junior_admin', 'Junior Admin'
    );

    $role = Role::load('junior_admin');
    $this->assertTrue($role->hasPermission('administer site configuration'), 'Junior Admin role has legacy permission.');

    $this->runUpdates();

    $role = Role::load('junior_admin');
    $this->assertTrue($role->hasPermission('administer site configuration'), 'Junior Admin role still has the legacy permission.');
    $this->assertTrue($role->hasPermission('see update notifications'), 'Junior Admin role now has the new permission.');
  }

}
