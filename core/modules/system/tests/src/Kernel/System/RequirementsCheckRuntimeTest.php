<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Kernel\System;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the effectiveness of hook_requirements_check during runtime().
 *
 * @group system
 */
class RequirementsCheckRuntimeTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system'];

  /**
   * Tests that hook_requirements_check is loaded in SystemManager.
   */
  public function testRequirementsCheckRuntime(): void {
    // Enable the test module.
    \Drupal::service('module_installer')->install(['module_runtime_requirements_check']);
    $testRequirements = [
      'title' => t('RuntimeError'),
      'value' => t('None'),
      'description' => t("Runtime Error."),
      'severity' => REQUIREMENT_ERROR,
    ];
    $requirements = \Drupal::service('system.manager')->listRequirements()['test.runtime.error'];
    $this->assertEquals($testRequirements, $requirements);
  }

  /**
   * Tests that hook_requirements_check_alter is loaded in SystemManager.
   */
  public function testRequirementsCheckAlterRuntime(): void {
    // Enable the test module.
    \Drupal::service('module_installer')->install(['module_runtime_requirements_check']);
    $testRequirementsAlter = [
      'title' => t('RuntimeWarning'),
      'value' => t('None'),
      'description' => t("Runtime Warning."),
      'severity' => REQUIREMENT_WARNING,
    ];
    $requirements = \Drupal::service('system.manager')->listRequirements()['test.runtime.error.alter'];
    $this->assertEquals($testRequirementsAlter, $requirements);
  }

}
