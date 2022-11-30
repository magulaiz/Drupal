<?php

namespace Drupal\Core\Extension\Plugin\Validation\Constraint;

use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Validation\Plugin\Validation\Constraint\RegexConstraint;

/**
 * Checks that the value is a valid extension name.
 *
 * @Constraint(
 *   id = "ExtensionName",
 *   label = @Translation("Valid extension name", context = "Validation")
 * )
 */
class ExtensionNameConstraint extends RegexConstraint {

  /**
   * Constructs an ExtensionNameConstraint object.
   *
   * @param mixed ...$arguments
   *   Arguments to pass to the parent constructor.
   */
  public function __construct(...$arguments) {
    // Always use the regular expression that ExtensionDiscovery uses to find
    // valid extensions.
    array_splice($arguments, 0, 1, ExtensionDiscovery::PHP_FUNCTION_PATTERN);
    parent::__construct(...$arguments);
  }

}
