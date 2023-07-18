<?php

declare(strict_types=1);

namespace Drupal\Tests\user\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\Role;

/**
 * Tests adding and removing permissions via the UI.
 *
 * @group user
 */
class UserPermissionsAdminTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests granting and revoking permissions via the UI sorts permissions.
   */
  public function testPermissionsSorting(): void {
    $role = Role::create(['id' => 'test_role', 'label' => 'Test role']);
    // Start the role with a permission that is near the end of the alphabet.
    $role->grantPermission('view user email addresses');
    $role->save();

    $this->drupalLogin($this->drupalCreateUser([
      'administer permissions',
    ]));
    $this->drupalGet('admin/people/permissions');

    $this->assertSession()->statusCodeEquals(200);

    // Add a permission that is near the start of the alphabet.
    $this->submitForm([
      'test_role[change own username]' => 1,
    ], 'Save permissions');

    // Check that permissions are sorted alphabetically.
    $storage = \Drupal::entityTypeManager()->getStorage('user_role');
    /** @var \Drupal\user\Entity\Role $role */
    $role = $storage->loadUnchanged($role->id());
    $this->assertEquals([
      'change own username',
      'view user email addresses',
    ], $role->getPermissions());

    // Remove the first permission, resulting in a single permission in the first
    // key of the array.
    $this->submitForm([
      'test_role[change own username]' => 0,
    ], 'Save permissions');
    /** @var \Drupal\user\Entity\Role $role */
    $role = $storage->loadUnchanged($role->id());
    $this->assertEquals([
      'view user email addresses',
    ], $role->getPermissions());
  }

  /**
   * Confirms that PermissionsListFilter can filter the permissions UI listing.
   */
  public function testFilterPermissionsEvent() {
    \Drupal::service('module_installer')->install(['user_permissions_test']);
    $this->resetAll();
    $this->rebuildContainer();
    $this->drupalLogin($this->drupalCreateUser([
      'administer permissions',
    ]));
    $this->drupalGet('admin/people/permissions');
    $items = array_map(fn($item) => $item->getAttribute('for'),
      $this->getSession()->getPage()->findAll('css', 'tbody label[for^="edit-anonymous"], tbody label[for^="edit-authenticated"]'));

    // Just assert there are greater than 6 permissions available so we know
    // there are more overall permissions than what appears once the event
    // subscriber in `user_filtered_permissions_test` is running.
    $this->assertGreaterThan(6, $items);

    // Enable a module that filters all but permissions a, b, c.
    \Drupal::service('module_installer')->install(['user_filtered_permissions_test']);
    $this->resetAll();
    $this->rebuildContainer();


    $this->drupalGet('admin/people/permissions');
    $items = array_map(fn($item) => $item->getAttribute('for'),
      $this->getSession()->getPage()->findAll('css', 'tbody label[for^="edit-anonymous"], tbody label[for^="edit-authenticated"]'));
    sort($items);
    $this->assertCount(6, $items);

    // The PermissionsListFilterSubscriber
    $this->assertEquals([
      'edit-anonymous-a',
      'edit-anonymous-b',
      'edit-anonymous-c',
      'edit-authenticated-a',
      'edit-authenticated-b',
      'edit-authenticated-c',
    ], $items);
  }

}
