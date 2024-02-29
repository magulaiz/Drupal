<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validator for the Drupal 'NoMarkup' constraint.
 */
class NoMarkupConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    if (!$constraint instanceof NoMarkupConstraint) {
      throw new UnexpectedTypeException($constraint, NoMarkupConstraint::class);
    }
    if (NULL === $value) {
      return;
    }
    if (strip_tags($value) !== $value) {
      $this->context->buildViolation($constraint->markupPresentMessage)
        ->addViolation();
      return;
    }
  }

}
