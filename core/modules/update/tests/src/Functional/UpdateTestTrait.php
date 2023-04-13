<?php

namespace Drupal\Tests\update\Functional;

/**
 * Provides a trait to set system info and XML mappings.
 */
trait UpdateTestTrait {

  /**
   * Sets mocked installed modules config.
   *
   * It expects information about the installed modules.
   *
   * @param string[][] $installed_modules
   *   The mock installed modules array.
   *   In the format as the key to be the project name and an array of sub keys
   *   as value such as 'project' (which is just the project name), 'version',
   *   'hidden', for example:
   *   'drupal' => [
   *     'project' => 'drupal',
   *     'version' => '8.0.0',
   *     'hidden' => FALSE,
   *   ].
   * @param string[] $default_config
   *   (optional) The default config keys to be set for all the modules.
   */
  public function mockInstalledModules(array $installed_modules, array $default_config = []): void {
    if (!empty($default_config)) {
      $installed_modules = array_merge(['#all' => $default_config], $installed_modules);
    }
    $this->config('update_test.settings')->set('system_info', $installed_modules)->save();
  }

  /**
   * Sets XML mappings.
   *
   * The array that maps project names to availability scenarios to
   * fetch.
   *
   * @param string[] $xml_map
   *   The XML mappings.
   *   In the format as the key to be the project name and available release as
   *   its value, for example:
   *   'aaa_update_test' => '8.x-1.2'.
   */
  public function setXmlMap(array $xml_map): void {
    $this->config('update_test.settings')->set('xml_map', $xml_map)->save();
  }

}
