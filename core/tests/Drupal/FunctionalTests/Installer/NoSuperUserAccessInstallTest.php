<?php

declare(strict_types=1);

namespace Drupal\FunctionalTests\Installer;

use Drupal\user\Entity\User;

/**
 * Tests no superuser access and the installer.
 *
 * @group Installer
 */
class NoSuperUserAccessInstallTest extends SuperUserAccessInstallTestBase {

  /**
   * {@inheritdoc}
   */
  protected function getInstallCode(): string {
    return <<<PHP
      <?php
      function {$this->profile}_install() {
        \$user = \Drupal\user\Entity\User::load(1);
        \Drupal::state()->set('admin_permission_in_installer', \$user->hasPermission('administer software updates'));
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
    $this->assertFalse(User::load(1)->hasPermission('administer software updates'));
    $this->assertTrue(\Drupal::state()->get('admin_permission_in_installer'));
    $this->assertSession()->pageTextContains(static::NO_ACCESS_MESSAGE);
  }

}
