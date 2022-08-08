<?php

namespace Drupal\Tests\system\Kernel\Theme;

use Drupal\Core\Theme\ThemeManager;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\Core\Theme\ThemeManager
 * @group legacy
 */
class ThemeManagerDeprecationTest extends KernelTestBase {

  /**
   * @covers ::__construct
   */
  public function testOptionalParameterDeprecation(): void {
    $this->expectDeprecation('Calling Drupal\Core\Theme\ThemeManager::__construct without the $logger_factory argument is deprecated in drupal:10.1.0 and it will be required in drupal:11.0.0. See https://www.drupal.org/node/3160464');
    new ThemeManager(
      '',
      $this->container->get('theme.negotiator'),
      $this->container->get('theme.initialization'),
      $this->container->get('module_handler'),
    );
  }

}
