<?php

namespace Drupal\Tests\update\Functional;

/**
 * Provides a trait to set system info and XML mappings.
 *
 * @see update_test_system_info_alter
 * @see \Drupal\update_test\Controller\UpdateTestController::updateTest
 */
trait UpdateTestTrait {

  /**
   * Sets information about installed modules.
   *
   * @param string[][] $installed_modules
   *   The mock installed modules array.
   *   In the format as the key to be the extension name and an array of sub
   *   keys as the extensions info such as 'project' (which is just the project
   *   name), 'version', 'hidden', only the keys that are defined will be
   *   overwritten in the info.
   *   @see update_test_system_info_alter
   *   for example:
   *   'drupal' => [
   *     'project' => 'drupal',
   *     'version' => '8.0.0',
   *     'hidden' => FALSE,
   *   ].
   * @param string[] $default_config
   *   (optional) The default info keys to be set for all the modules.
   */
  public function mockInstalledModules(array $installed_modules, array $default_config = []): void {
    if (!empty($default_config)) {
      $installed_modules = array_merge(['#all' => $default_config], $installed_modules);
    }
    $this->config('update_test.settings')->set('system_info', $installed_modules)->save();
  }

  /**
   * Sets available release mappings.
   *
   * @param string[] $release_metadata
   *   The available release mappings. In the format as the key to be the
   *   extension name and available release as its value,
   *   @see \Drupal\update_test\Controller\UpdateTestController::updateTest
   *   for example:
   *   'drupal' => 'sec.0.2', which matches the release history xml file named
   *   drupal.sec.0.2.xml.
   */
  public function setAvailableReleasesMetadata(array $release_metadata): void {
    $this->config('update_test.settings')->set('xml_map', $release_metadata)->save();
  }

}
