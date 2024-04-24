<?php

declare(strict_types=1);

namespace Drupal\FunctionalTests\Installer;

use Drupal\Core\Serialization\Yaml;
use Drupal\user\Entity\User;

/**
 * Tests superuser access and the installer.
 *
 * @group Installer
 */
class SuperUserAccessTest extends InstallerTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'superuser';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function prepareEnvironment() {
    parent::prepareEnvironment();
    $info = [
      'type' => 'profile',
      'core_version_requirement' => '*',
      'name' => 'Superuser testing profile',
    ];
    // File API functions are not available yet.
    $path = $this->siteDirectory . '/profiles/' . $this->profile;
    mkdir($path, 0777, TRUE);
    file_put_contents("$path/{$this->profile}.info.yml", Yaml::encode($info));
    $install_code = <<<PHP
      <?php
      function {$this->profile}_install() {
        \$user = \Drupal\user\Entity\User::load(1);
        \Drupal::state()->set('admin_permission_in_installer', \$user->hasPermission('administer software updates'));
      }
      PHP;

    file_put_contents("$path/{$this->profile}.install", $install_code);

    $services = Yaml::decode(file_get_contents(DRUPAL_ROOT . '/sites/default/default.services.yml'));
    $services['parameters']['security.enable_super_user'] = FALSE;
    file_put_contents(DRUPAL_ROOT . '/' . $this->siteDirectory . '/services.yml', Yaml::encode($services));
  }

  /**
   * Confirms that the installation succeeded.
   */
  public function testInstalled() {
    $this->assertFalse(User::load(1)->hasPermission('administer software updates'));
    $this->assertTrue(\Drupal::state()->get('admin_permission_in_installer'));
    $this->assertSession()->pageTextContains('User 1 does not have administrator access.');
  }

}
