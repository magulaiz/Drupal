<?php

declare(strict_types=1);

namespace Drupal\module_update_requirements_check\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for module_runtime_requirements.
 */
class RequirementsCheckHook {

  /**
 * Implements hook_requirements_check().
 */
  #[Hook('requirements_check')]
  public function requirementsCheckUpdate(): array {
    return [
      'test.update.error' => [
        'title' => t('UpdateError'),
        'value' => t('None'),
        'description' => t("Update Error."),
        'severity' => REQUIREMENT_ERROR,
      ],
      'test.update.error.alter' => [
        'title' => t('UpdateError'),
        'value' => t('None'),
        'description' => t("Update Error."),
        'severity' => REQUIREMENT_ERROR,
      ],
    ];
  }

  /**
   * Implements hook_requirements_check_alter().
   */
  #[Hook('requirements_check_alter')]
  public function requirementsCheckAlterUpdate(array &$requirements): void {
    $requirements['test.update.error.alter'] = [
      'title' => t('UpdateWarning'),
      'value' => t('None'),
      'description' => t("Update Warning."),
      'severity' => REQUIREMENT_WARNING,
    ];
  }

}
