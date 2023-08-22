<?php

namespace Drupal\Core\Extension\Requirement;

/**
 * Provides a RequirementInfo value object.
 */
class RequirementInfo extends BaseRequirement {

  /**
   * The severity.
   *
   * @var int
   */
  protected $severity = self::SEVERITY_INFO;

}
