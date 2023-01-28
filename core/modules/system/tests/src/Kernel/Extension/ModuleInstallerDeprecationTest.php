<?php

namespace Drupal\Tests\system\Kernel\Extension;

use Drupal\Core\Extension\ModuleInstaller;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\Core\Extension\ModuleInstaller
 * @group legacy
 */
class ModuleInstallerDeprecationTest extends KernelTestBase {

  /**
   * @covers ::__construct
   */
  public function testOptionalParameterDeprecation(): void {
    $this->expectDeprecation('Calling Drupal\Core\Extension\ModuleInstaller::__construct without the $logger_factory argument is deprecated in drupal:10.1.0 and it will be required in drupal:11.0.0. See https://www.drupal.org/node/3160464');
    new ModuleInstaller(
      '',
      $this->container->get('module_handler'),
      $this->container->get('kernel'),
      $this->container->get('database'),
      $this->container->get('update.update_hook_registry'),
    );
  }

}
