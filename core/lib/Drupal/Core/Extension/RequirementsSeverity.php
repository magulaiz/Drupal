<?php

namespace Drupal\Core\Extension;

/**
 * A helper class for requirements severity.
 */
final class RequirementsSeverity {

  /**
   * Returns the maximum severity of a set of requirements.
   *
   * @param array $requirements
   *   An array of requirements, in the same format as is returned by
   *   hook_requirements().
   *
   * @return int
   *   The highest severity in the array.
   */
  public static function getMaxSeverity(array $requirements): int {
    $severity = REQUIREMENT_OK;
    foreach ($requirements as $requirement) {
      if (isset($requirement['severity'])) {
        $severity = max($severity, $requirement['severity']);
      }
    }
    return $severity;
  }

}
