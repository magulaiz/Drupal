<?php

declare(strict_types=1);

namespace Drupal\package_manager\Install\Requirements;

use Drupal\Core\Extension\InstallRequirementsInterface;
use Drupal\Core\Site\Settings;

/**
 * Install time requirements for the package_manager module.
 */
class PackageManagerRequirements implements InstallRequirementsInterface {

  /**
   * {@inheritdoc}
   */
  public static function getRequirements(): array {
    $requirements = [];

    if (Settings::get('testing_package_manager', FALSE) === FALSE) {
      $requirements['testing_package_manager'] = [
        'title' => 'Package Manager',
        'description' => t("Package Manager is available for early testing. To install the module set the value of 'testing_package_manager' to TRUE in your settings.php file."),
        'severity' => REQUIREMENT_ERROR,
      ];
      return $requirements;
    }

    return $requirements;
  }

}
