<?php

declare(strict_types=1);

namespace Drupal\FunctionalTests\Installer;

use Drupal\user\Entity\User;

/**
 * Tests superuser access and the installer.
 *
 * @group Installer
 */
class NoSuperUserAccessWithAdminRoleInstallTest extends SuperUserAccessInstallTestBase {

  /**
   * {@inheritdoc}
   */
  protected function getInstallCode(): string {
    return <<<PHP
      <?php
      function {$this->profile}_install() {
        \$user = \Drupal\user\Entity\User::load(1);
        \Drupal::state()->set('admin_permission_in_installer', \$user->hasPermission('administer software updates'));
        \Drupal\user\Entity\Role::create(['id' => 'admin_role', 'label' => 'Admin role'])->setIsAdmin(TRUE)->save();
      }
      PHP;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSuperUserPolicy(): bool {
    return FALSE;
  }

  /**
   * Confirms that the installation succeeded.
   */
  public function testInstalled(): void {
    $user = User::load(1);
    $this->assertTrue($user->hasPermission('administer software updates'));
    $this->assertTrue($user->hasRole('admin_role'));
    $this->assertTrue(\Drupal::state()->get('admin_permission_in_installer'));
    $this->assertSession()->pageTextNotContains(static::NO_ACCESS_MESSAGE);
  }

}
