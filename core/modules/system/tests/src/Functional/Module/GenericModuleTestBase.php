<?php

namespace Drupal\Tests\system\Functional\Module;

use Drupal\Tests\BrowserTestBase;

/**
 * Runs a series of generic tests for one module.
 */
abstract class GenericModuleTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'help',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Get the module name.
   *
   * @return string
   *   The module to test.
   */
  protected function getModule(): string {
    return explode('\\', get_class($this))[2];
  }

  /**
   * Checks some generic things about a module.
   */
  public function testModuleGenericIssues(): void {
    $module = $this->getModule();
    \Drupal::service('module_installer')->install([$module]);
    $info = \Drupal::service('extension.list.module')->getExtensionInfo($module);
    if (!empty($info['required']) && !empty($info['hidden'])) {
      $this->markTestSkipped('Nothing to assert for hidden, required modules.');
    }
    $this->drupalLogin($this->rootUser);
    $this->assertHookHelp($module);

    if (empty($info['required'])) {
      // Check that the module can be uninstalled and then re-installed again.
      $this->preUnInstallSteps();
      \Drupal::service('module_installer')->uninstall([$module]);
      \Drupal::service('module_installer')->install([$module]);
    }
  }

  /**
   * Verifies hook_help() syntax.
   *
   * @param string $module
   *   The module.
   */
  protected function assertHookHelp(string $module): void {
    $info = \Drupal::service('extension.list.module')->getExtensionInfo($module);
    if (empty($info['hidden'])) {
      $this->drupalGet('admin/help/' . $module);
      $this->assertSession()->statusCodeEquals(200);
      $this->assertSession()->pageTextContains($info['name'] . ' module');
      $this->assertSession()->linkExists('online documentation for the ' . $info['name'] . ' module', 0, "Correct online documentation link is in the help page for $module");
    }
  }

  /**
   * Helper to perform any steps required prior to uninstalling a module.
   */
  protected function preUnInstallSteps(): void {}

}
