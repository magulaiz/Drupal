<?php

namespace Drupal\KernelTests\Config;

/**
 * Tests that deprecated extensions' installed and default config match.
 *
 * @group Config
 *
 * @legacy
 *   This tests the functionality of deprecated extensions. It should not be
 *   removed from core during major version cleanups.
 */
class DeprecatedExtensionDefaultConfigTest extends DefaultConfigTestBase {

  /**
   * Tests that the installed config is equal to the exported config.
   *
   * @dataProvider moduleListDataProvider
   */
  public function testModuleConfig($module) {
    $info = $this->parseExtensionInfo($module, 'module');
    if (!$this->isDeprecated($info)) {
      $this->markTestSkipped("The $module module is not deprecated.");
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
    if (!$this->isDeprecated($info)) {
      $this->markTestSkipped("The $theme theme is not deprecated.");
    }
    $this->assertExtensionConfig($theme, 'theme');
  }

  /**
   * A data provider that lists every core module plus a deprecated test module.
   *
   * Also adds a deprecated module with config.
   *
   * @return string[][]
   *   An array of module names to test, with both key and value being the name
   *   of the module.
   */
  public function moduleListDataProvider() {
    $modules_keyed = $this->coreModuleListDataProvider();

    // Ensure there is always at least one deprecated module with config.
    $modules_keyed['deprecated_module'] = ['deprecated_module'];

    return $modules_keyed;
  }

  /**
   * A data provider that lists every core theme plus a deprecated test theme.
   *
   * @return string[][]
   *   An array of theme names to test, with both key and value being the name
   *   of the theme.
   */
  public function themeListDataProvider() {
    $themes = parent::themeListDataProvider();
    $themes['test_deprecated_theme'] = ['test_deprecated_theme'];
    return $themes;
  }

  /**
   * {@inheritdoc}
   */
  protected function parseExtensionInfo(string $name, string $type): array {
    // Parse .info.yml file for module/theme $name. Since it's not installed at
    // this point we can't retrieve it from the 'module_handler' service.
    switch ($name) {
      case 'test_deprecated_theme':
        $file_name = DRUPAL_ROOT . '/core/modules/system/tests/themes/' . $name . '/' . $name . '.info.yml';
        break;

      case 'deprecated_module':
        $file_name = DRUPAL_ROOT . '/core/modules/system/tests/modules/' . $name . '/' . $name . '.info.yml';
        break;

      default;
        $file_name = DRUPAL_ROOT . '/core/' . $type . 's/' . $name . '/' . $name . '.info.yml';
    }

    $info = \Drupal::service('info_parser')->parse($file_name);

    // Test we have a parsed info.yml file.
    $this->assertNotEmpty($info);

    return $info;
  }

}
