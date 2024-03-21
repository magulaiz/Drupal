<?php

declare(strict_types=1);

namespace Drupal\FunctionalTests\Installer;

// cspell:ignore enregistrer
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Archiver\ArchiveTar;
use Drupal\Core\Database\Database;

/**
 * Verifies that installing from existing configuration without a profile works.
 *
 * @group Installer
 */
class InstallerExistingConfigNoProfileTest extends InstallerExistingConfigTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $existingSyncDirectory = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function prepareEnvironment() {
    // Skip the prepare environment from InstallerExistingConfigTestBase.
    InstallerTestBase::prepareEnvironment();
    $archiver = new ArchiveTar($this->getConfigTarball(), 'gz');

    $config_sync_directory = $this->siteDirectory . '/config/sync';
    $this->settings['settings']['config_sync_directory'] = (object) [
      'value' => $config_sync_directory,
      'required' => TRUE,
    ];

    // Create config/sync directory and extract tarball contents to it.
    mkdir($config_sync_directory, 0777, TRUE);
    $files = [];
    $list = $archiver->listContent();
    if (is_array($list)) {
      /** @var array $list */
      foreach ($list as $file) {
        $files[] = $file['filename'];
      }
      $archiver->extractList($files, $config_sync_directory);
    }

    // Add the module that is providing the database driver to the list of
    // modules that can not be uninstalled in the core.extension configuration.
    if (file_exists($config_sync_directory . '/core.extension.yml')) {
      $core_extension = Yaml::decode(file_get_contents($config_sync_directory . '/core.extension.yml'));
      $module = Database::getConnection()->getProvider();
      if ($module !== 'core') {
        $core_extension['module'][$module] = 0;
        $core_extension['module'] = module_config_sort($core_extension['module']);
      }
      if (array_key_exists('profile', $core_extension)) {
        // Remove the profile.
        unset($core_extension['module'][$core_extension['profile']]);
        unset($core_extension['profile']);
      }

      file_put_contents($config_sync_directory . '/core.extension.yml', Yaml::encode($core_extension));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function setUpLanguage() {
    // Place a custom local translation in the translations directory.
    mkdir($this->root . '/' . $this->siteDirectory . '/files/translations', 0777, TRUE);
    file_put_contents($this->root . '/' . $this->siteDirectory . '/files/translations/drupal-8.0.0.fr.po', "msgid \"\"\nmsgstr \"\"\nmsgid \"Save and continue\"\nmsgstr \"Enregistrer et continuer\"");
    parent::setUpLanguage();
  }

  /**
   * {@inheritdoc}
   */
  public function setUpSettings() {
    // The configuration is from a site installed in French.
    // So after selecting the profile the installer detects that the site must
    // be installed in French, thus we change the button translation.
    $this->translations['Save and continue'] = 'Enregistrer et continuer';
    parent::setUpSettings();
  }

  /**
   * {@inheritdoc}
   */
  protected function getConfigTarball() {
    return __DIR__ . '/../../../fixtures/config_install/testing_config_install.tar.gz';
  }

}
