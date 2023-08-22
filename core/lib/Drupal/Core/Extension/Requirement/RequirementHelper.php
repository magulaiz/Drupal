<?php


namespace Drupal\Core\Extension\Requirement;

/**
 * Provides a helper for requirements.
 */
class RequirementHelper {

  /**
   * Extracts the highest severity from the requirements array.
   *
   * @param \Drupal\Core\Extension\Requirement\RequirementInterface[] $requirements
   *   An array of requirements.
   *
   * @return int
   *   The highest severity in the array.
   */
  public static function getMaxSeverity(array $requirements) {
    return array_reduce($requirements, function ($severity, RequirementInterface $requirement) {
      return max($severity, $requirement->getSeverity());
    }, RequirementInterface::SEVERITY_OK);
  }

}
