<?php

namespace Drupal\path\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint for changing path aliases in pending revisions.
 *
 * @Constraint(
 *   id = "PathAliasOverride",
 *   label = @Translation("Path alias override"),
 * )
 */
class PathAliasOverrideConstraint extends Constraint {

  public $message = 'The alias "%alias" matches a existing system path. Please try a different alias.';

}
