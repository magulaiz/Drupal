<?php

declare(strict_types=1);

namespace Drupal\Core\Extension\Requirement;

/**
 * A helper class for requirements severity.
 */
enum RequirementSeverity: int {

  // Informational message only.
  case INFO = -1;
  // Requirement successfully met.
  case OK = 0;
  // Warning condition; proceed but flag warning.
  case WARNING = 1;
  // Error condition; abort installation.
  case ERROR = 2;

  /**
   * Returns the maximum severity of a set of requirements.
   *
   * @param array $requirements
   *   An array of requirements, in the same format as is returned by
   *   hook_requirements().
   *
   * @return RequirementSeverity
   *   The highest severity in the array.
   */
  public static function getMaxSeverity(array $requirements): RequirementSeverity {
    return array_reduce(
      array: $requirements,
      callback: function (RequirementSeverity $severity, array $requirement) {
        if (is_int($requirement['severity'])) {
          @\trigger_error('Calling ' . __METHOD__ . '() with \'severity\' as int values instead of RequirementSeverity enums is deprecated in drupal:10.3.0 and is required in drupal:11.0.0. See https://www.drupal.org/node/3410939', \E_USER_DEPRECATED);
          $requirementSeverity = $requirement['severity'];
        }
        else {
          $requirementSeverity = $requirement['severity']->value;
        }
        return RequirementSeverity::from(max($severity->value, $requirementSeverity));
      },
      initial: RequirementSeverity::OK);
  }

}
