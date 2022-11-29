<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ContainsRegexMatchConstraintValidator extends ConstraintValidator {

  public function validate(mixed $value, Constraint $constraint) {
    if (!is_array($value)) {
      throw new UnexpectedTypeException($value, 'array');
    }

    $results = preg_grep($constraint->pattern, $value);
    if (empty($results)) {
      $this->context->addViolation($constraint->message, [
        '@pattern' => $constraint->pattern,
      ]);
    }
  }

}
