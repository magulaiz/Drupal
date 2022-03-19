<?php

namespace Drupal\KernelTests\Config;

/**
 * Tests that non-deprecated extensions' installed and default config match.
 *
 * @group Config
 */
class DefaultConfigTest extends DefaultConfigTestBase {

  /**
   * Tests that the installed config is equal to the exported config.
   *
   * @dataProvider coreModuleListDataProvider
   */
  public function testModuleConfig($module) {
    $info = $this->parseExtensionInfo($module, 'module');
    if ($this->isDeprecated($info)) {
      $this->markTestSkipped("The $module module is deprecated.");
    }
    $this->assertExtensionConfig($module, 'module');
  }

  /**
   * Tests that the installed config is equal to the exported config.
   *
   * @dataProvider themeListDataProvider
   */
  public function testThemeConfig($theme) {
    $info = $this->parseExtensionInfo($theme, 'theme');
    if ($this->isDeprecated($info)) {
      $this->markTestSkipped("The $theme theme is deprecated.");
    }
    $this->assertExtensionConfig($theme, 'theme');
  }

}
