<?php

namespace Drupal\Core\Extension\Requirement;

/**
 * Provides a RequirementError value object.
 */
class RequirementError extends BaseRequirement {

  /**
   * The severity.
   *
   * @var int
   */
  protected $severity = self::SEVERITY_ERROR;

}
