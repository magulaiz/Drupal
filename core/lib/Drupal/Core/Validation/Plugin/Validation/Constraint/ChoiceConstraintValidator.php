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

    assert($constraint instanceof ChoiceConstraint);
    if ($constraint->callbackArgs) {

      // Check for a callable in the service:method notation.
      $service = NULL;
      $method = NULL;
      $count = substr_count($constraint->callback, ':');
      if ($count == 1) {
        [$service, $method] = explode(':', $constraint->callback, 2);
      }
      $drupal_service = \Drupal::service($service);
      $callback = function() {
        // @todo;
      };
    }
    // $constraint->callback = $callback;
    parent::validate($value, $constraint);
  }

}
