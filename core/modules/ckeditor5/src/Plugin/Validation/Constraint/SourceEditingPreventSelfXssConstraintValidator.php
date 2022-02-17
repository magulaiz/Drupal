<?php

declare(strict_types = 1);

namespace Drupal\ckeditor5\Plugin\Validation\Constraint;

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 *
 * @internal
 */
class SourceEditingPreventSelfXssConstraintVAlidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   *
   * @throws \Symfony\Component\Validator\Exception\UnexpectedTypeException
   *   Thrown when the given constraint is not supported by this validator.
   */
  public function validate($value, Constraint $constraint) {
    if (!$constraint instanceof SourceEditingPreventSelfXssConstraint) {
      throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\SourceEditingPreventSelfXssConstraint');
    }
    if (empty($value)) {
      return;
    }

    // @todo
  }

}
