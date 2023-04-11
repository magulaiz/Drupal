<?php

namespace Drupal\Tests\update\Functional;

use Drupal\Core\Test\TestSetupTrait;

/**
 * Provides a trait to set system info and xml mappings.
 */
trait UpdateTestTrait {
  use TestSetupTrait;

  /**
   * Sets system info.
   *
   * @param array $system_info
   *   The system info.
   */
  public function setSystemInfo(array $system_info): void {
    $this->config('update_test.settings')->set('system_info', $system_info)->save();
  }

  /**
   * Sets xml mappings.
   *
   * @param array $xml_map
   *   The xml mappings.
   */
  public function setXmlMap(array $xml_map): void {
    $this->config('update_test.settings')->set('system_info', $xml_map)->save();
  }

  /**
   * Configuration accessor for tests. Returns non-overridden configuration.
   *
   * @param string $name
   *   Configuration name.
   *
   * @return \Drupal\Core\Config\Config
   *   The configuration object with original configuration data.
   */
  protected function config($name) {
    return $this->container->get('config.factory')->getEditable($name);
  }

}
