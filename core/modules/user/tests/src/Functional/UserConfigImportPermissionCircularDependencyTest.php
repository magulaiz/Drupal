<?php

namespace Drupal\Tests\config\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\Role;

/**
 * Tests that dynamic permissions based on a config entity is correctly imported.
 *
 * @group user
 */
class UserConfigImportPermissionCircularDependencyTest extends BrowserTestBase {

  /**
   * The profile to install as a basis for testing.
   *
   * @var string
   */
  protected $profile = 'testing_config_install_circular_perm_dependency';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->webUser = $this->drupalCreateUser(['synchronize configuration']);
    $this->drupalLogin($this->webUser);
    $this->copyConfig($this->container->get('config.storage'), $this->container->get('config.storage.sync'));
  }

  /**
   * Tests that dynamic permissions based on config is correctly imported.
   */
  public function testInstallProfileValidation(): void {
    $role = Role::load('role_1');
    $this->assertEquals($role->hasPermission('role_1'));
  }

}
