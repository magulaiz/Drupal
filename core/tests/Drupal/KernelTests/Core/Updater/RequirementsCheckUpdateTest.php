<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Updater;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests hook_requirements_check during update.
 *
 * @group Hooks
 */
class RequirementsCheckUpdateTest extends KernelTestBase {

  /**
   * Tests hook_requirements_check.
   */
  public function testRequirementsCheckUpdate(): void {
    require_once 'core/includes/update.inc';

    \Drupal::service('module_installer')->install(['module_update_requirements_check']);
    $testRequirements = [
      'title' => t('UpdateError'),
      'value' => t('None'),
      'description' => t("Update Error."),
      'severity' => REQUIREMENT_ERROR,
    ];
    $requirements = update_check_requirements()['test.update.error'];
    $this->assertEquals($testRequirements, $requirements);
  }

  /**
   * Tests hook_requirements_check_alter.
   */
  public function testRequirementsCheckAlterUpdate(): void {
    require_once 'core/includes/update.inc';

    \Drupal::service('module_installer')->install(['module_update_requirements_check']);
    $testRequirements = [
      'title' => t('UpdateWarning'),
      'value' => t('None'),
      'description' => t("Update Warning."),
      'severity' => REQUIREMENT_WARNING,
    ];
    $requirements = update_check_requirements()['test.update.error.alter'];
    $this->assertEquals($testRequirements, $requirements);
  }

}
