<?php

namespace Drupal\Core\Extension\Requirement;

/**
 * Provides a RequirementWarning value object.
 */
class RequirementWarning extends BaseRequirement {

  /**
   * The severity.
   *
   * @var int
   */
  protected $severity = self::SEVERITY_WARNING;

}
