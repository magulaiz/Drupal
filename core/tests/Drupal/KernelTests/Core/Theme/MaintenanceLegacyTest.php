<?php

namespace Drupal\KernelTests\Core\Theme;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests themes and base themes are correctly loaded.
 *
 * @group legacy
 */
class MaintenanceLegacyTest extends KernelTestBase {

  /**
   * Test deprecation of drupal_maintenance_theme() function.
   */
  public function testMaintenanceThemeFunction() {
    $this->expectDeprecation("drupal_maintenance_theme() is deprecated in drupal:9.1.0 and is removed from drupal:10.0.0. Use \Drupal::service('maintenance_mode')->setTheme() instead. See https://www.drupal.org/node/3058979");
    $this->setSetting('maintenance_theme', 'test_subtheme');
    // Get the maintenance theme loaded.
    drupal_maintenance_theme();

    // Do we have an active theme?
    $this->assertTrue(\Drupal::theme()->hasActiveTheme());

    $active_theme = \Drupal::theme()->getActiveTheme();
    $this->assertEquals('test_subtheme', $active_theme->getName());

    $base_themes = $active_theme->getBaseThemeExtensions();
    $base_theme_names = array_keys($base_themes);
    $this->assertSame(['test_basetheme'], $base_theme_names);
  }

  /**
   * Test deprecation of _drupal_maintenance_theme() function.
   */
  public function testMaintenanceThemeInternalFunction() {
    $this->expectDeprecation("_drupal_maintenance_theme() is deprecated in drupal:9.1.0 and is removed from drupal:10.0.0. Use \Drupal::service('maintenance_mode')->setTheme() instead. See https://www.drupal.org/node/3058979");

    $this->setSetting('maintenance_theme', 'test_subtheme');
    // Get the maintenance theme loaded.
    include_once $this->root . '/core/includes/theme.maintenance.inc';
    _drupal_maintenance_theme();

    // Do we have an active theme?
    $this->assertTrue(\Drupal::theme()->hasActiveTheme());

    $active_theme = \Drupal::theme()->getActiveTheme();
    $this->assertEquals('test_subtheme', $active_theme->getName());

    $base_themes = $active_theme->getBaseThemeExtensions();
    $base_theme_names = array_keys($base_themes);
    $this->assertSame(['test_basetheme'], $base_theme_names);
  }

}
