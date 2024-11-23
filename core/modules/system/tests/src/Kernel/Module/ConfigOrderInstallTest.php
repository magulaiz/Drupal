<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Kernel\Module;

use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test that module installation properly orders optional and required config.
 *
 * @group Module
 */
class ConfigOrderInstallTest extends KernelTestBase {

  /**
   * The module installer service.
   */
  protected ModuleInstallerInterface $moduleInstaller;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->moduleInstaller = $this->container->get('module_installer');
    $this->moduleInstaller->install([
      'system',
      'user',
    ]);
  }

  /**
   * Test that install config can depend on optional config.
   */
  public function testInstallConfigCanDependOnOptional() : void {
    // Pretend we're in install mode while this module is getting installed.
    $GLOBALS['install_state'] = ['installation_finished' => FALSE];

    $this->moduleInstaller->install(['config_install_test_optional_dependent']);

    unset($GLOBALS['install_state']);

    // For some reason Drupal breaks $this->expectNotToPerformAssertions() but
    // this test passes if the installer doesn't throw a config dependency
    // exception.
    $this->assertTrue(TRUE);
  }

}
