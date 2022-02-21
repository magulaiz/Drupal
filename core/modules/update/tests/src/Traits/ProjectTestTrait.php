<?php

namespace Drupal\Tests\update\Traits;

/**
 * Provides a trait for or setting project versions.
 */
trait ProjectTestTrait {

  /**
   * Sets the project versions.
   *
   * @param array $system_info
   *   The system information.
   *
   * @see update_test_system_info_alter()
   */
  protected function setProjectsInfo(array $system_info): void {
    $this->config('update_test.settings')->set('system_info', $system_info)->save();
  }

}
