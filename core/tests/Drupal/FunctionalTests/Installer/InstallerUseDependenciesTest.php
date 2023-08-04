<?php

namespace Drupal\FunctionalTests\Installer;

/**
 * Verifies that profiles invalid config can not be installed.
 *
 * @group Installer
 */
class InstallerUseDependenciesTest extends InstallerTestBase {

  protected $profile = 'testing_install_use_dependencies';

  /**
   * Confirms that the installation succeeded.
   */
  public function testInstalled() {
    $this->assertResponse(200);
  }

}
