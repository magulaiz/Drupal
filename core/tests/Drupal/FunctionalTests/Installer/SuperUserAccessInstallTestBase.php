<?php

declare(strict_types=1);

namespace Drupal\FunctionalTests\Installer;

use Drupal\Core\Serialization\Yaml;

/**
 * Aids testing the super user access policy in the installer.
 *
 * @group Installer
 */
abstract class SuperUserAccessInstallTestBase extends InstallerTestBase {

  /**
   * Message when the logged-in user does not have admin access after install.
   *
   * @see \Drupal\Core\Installer\Form\SiteConfigureForm::submitForm())
   */
  protected const NO_ACCESS_MESSAGE = 'User 1 does not have administrator access.';

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

    file_put_contents("$path/{$this->profile}.install", $this->getInstallCode());

    $services = Yaml::decode(file_get_contents(DRUPAL_ROOT . '/sites/default/default.services.yml'));
    $services['parameters']['security.enable_super_user'] = $this->getSuperUserPolicy();
    file_put_contents(DRUPAL_ROOT . '/' . $this->siteDirectory . '/services.yml', Yaml::encode($services));
  }

  /**
   * Confirms that the installation succeeded.
   */
  abstract public function testInstalled(): void;

  /**
   * Gets code for the install profile's install file.
   *
   * @return string
   */
  abstract protected function getInstallCode(): string;

  /**
   * Gets the value for the super user policy container parameter.
   *
   * @return bool
   */
  abstract protected function getSuperUserPolicy(): bool;

}
