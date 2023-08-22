<?php

namespace Drupal\Core\Extension\Requirement;

/**
 * Provides a RequirementOk value object.
 */
class RequirementOk extends BaseRequirement {

  /**
   * The severity.
   *
   * @var int
   */
  protected $severity = self::SEVERITY_OK;

}
