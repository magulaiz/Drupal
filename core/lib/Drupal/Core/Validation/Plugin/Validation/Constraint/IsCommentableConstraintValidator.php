<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates that the entity is commentable.
 */
class IsCommentableConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint): void {
    assert($constraint instanceof IsCommentableConstraint);

    if (!is_string($value)) {
      $this->context->addViolation($constraint->valueIsNoStringMessage);
      return;
    }

    if (!_comment_entity_uses_integer_id($value)) {
      $this->context->addViolation($constraint->message, ['@entity_type_id' => $value]);
    }
  }

}
