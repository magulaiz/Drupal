<?php

namespace Drupal\FunctionalTests\Installer;

/**
 * Verifies that profiles invalid config can not be installed.
 *
 * @group Installer
 */
class InstallerUseDependenciesTest extends InstallerTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'starterkit_theme';

  /**
   * {@inheritdoc}
   */
  protected $profile = 'testing_install_use_dependencies';

  /**
   * Confirms that the installation succeeded.
   */
  public function testInstalled() {
    $this->assertSession()->statusCodeEquals(200);
  }

}
