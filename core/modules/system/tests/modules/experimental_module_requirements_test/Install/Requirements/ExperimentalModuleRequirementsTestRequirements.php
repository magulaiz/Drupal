<?php

declare(strict_types=1);

namespace Drupal\experimental_module_requirements_test\Install\Requirements;

use Drupal\Core\Extension\InstallRequirementsInterface;

/**
 * Install time requirements for the experimental_module_requirements_test module.
 */
class ExperimentalModuleRequirementsTestRequirements implements InstallRequirementsInterface {

  /**
   * {@inheritdoc}
   */
  public static function getRequirements(): array {
    $requirements = [];
    if (\Drupal::state()->get('experimental_module_requirements_test_requirements', FALSE)) {
      $requirements['experimental_module_requirements_test_requirements'] = [
        'severity' => REQUIREMENT_ERROR,
        'description' => t('The Experimental Test Requirements module can not be installed.'),
      ];
    }
    return $requirements;
  }

}
