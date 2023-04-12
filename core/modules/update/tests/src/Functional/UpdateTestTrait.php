<?php

namespace Drupal\Tests\update\Functional;

/**
 * Provides a trait to set system info and XML mappings.
 */
trait UpdateTestTrait {

  /**
   * Sets system info.
   *
   * It expects information about the installed modules.
   *
   * @param string[] $system_info
   *   The system info.
   *   In the format as the key to be the project name and an array of sub keys
   *   as value such as 'project' (which is just the project name), 'version',
   *   'hidden', for example:
   *   'drupal' => [
   *     'project' => 'drupal',
   *     'version' => '8.0.0',
   *     'hidden' => FALSE,
   *   ].
   */
  public function setSystemInfo(array $system_info): void {
    $this->config('update_test.settings')->set('system_info', $system_info)->save();
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
