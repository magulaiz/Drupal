<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\TypedData\Validation\TypedDataAwareValidatorTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ChoiceValidator;

/**
 * Validates complex data.
 */
class ChoiceConstraintValidator extends ChoiceValidator {

  use TypedDataAwareValidatorTrait;

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {

    if (!$constraint instanceof ChoiceConstraint) {
      throw new UnexpectedTypeException($constraint, ChoiceConstraint::class);
    }
    assert($constraint instanceof ChoiceConstraint);

    if ($constraint->callbackArgs) {
      $callback = function () {
        // @todo;
      };
    }
    $constraint->callback = $callback;
    parent::validate($value, $constraint);
  }

}
