<?php

declare(strict_types=1);

namespace Drupal\module_runtime_requirements_check\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for module_runtime_requirements_check.
 */
class RequirementsCheckHook {

  /**
   * Implements hook_requirements_check().
   */
  #[Hook('requirements_check')]
  public function requirementsCheck(): array {
    return [
      'test.runtime.error' => [
        'title' => t('RuntimeError'),
        'value' => t('None'),
        'description' => t("Runtime Error."),
        'severity' => REQUIREMENT_ERROR,
      ],
      'test.runtime.error.alter' => [
        'title' => t('RuntimeError'),
        'value' => t('None'),
        'description' => t("Runtime Error."),
        'severity' => REQUIREMENT_ERROR,
      ],
    ];
  }

  /**
   * Implements hook_requirements_check_alter().
   */
  #[Hook('requirements_check_alter')]
  public function requirementsCheckAlter(array &$requirements): void {
    $requirements['test.runtime.error.alter'] = [
      'title' => t('RuntimeWarning'),
      'value' => t('None'),
      'description' => t("Runtime Warning."),
      'severity' => REQUIREMENT_WARNING,
    ];
  }

}
