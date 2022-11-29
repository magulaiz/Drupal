<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the ContainsRegexMatch constraint.
 */
class ContainsRegexMatchConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    if (!is_array($value)) {
      throw new UnexpectedTypeException($value, 'array');
    }

    $pattern = $constraint->pattern;

    $results = preg_grep($pattern, $value);
    if ($results === FALSE) {
      throw new LogicException("Invalid regular expression: '$pattern'");
    }
    elseif (empty($results)) {
      $this->context->addViolation($constraint->message, [
        '@pattern' => $pattern,
      ]);
    }
  }

}
