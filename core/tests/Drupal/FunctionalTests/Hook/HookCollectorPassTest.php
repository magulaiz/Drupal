<?php

declare(strict_types=1);

namespace Drupal\FunctionalTests\Hook;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests services in .module files.
 *
 * @group Hook
 */
class HookCollectorPassTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests Hook Collector Pass.
   */
  public function testHookCollectorModuleServices(): void {
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $module_installer */
    $module_installer = $this->container->get('module_installer');
    // Install module with service in .module outside of function.
    $this->assertTrue($module_installer->install(['hook_collector_skip_procedural_attribute']));
  }

}
