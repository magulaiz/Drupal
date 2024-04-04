<?php

declare(strict_types=1);

namespace Drupal\Core\Config\Plugin\Validation\Constraint;

use Drupal\Core\Config\Schema\Mapping;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;

/**
 * Validates the LangcodeRequiredIfTranslatableValues constraint.
 */
final class LangcodeRequiredIfTranslatableValuesConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    assert($constraint instanceof LangcodeRequiredIfTranslatableValuesConstraint);

    if ($this->context->getObject() !== $this->context->getRoot()) {
      throw new LogicException('The LangcodeRequiredIfTranslatableValues constraint can only operate on the root object being validated.');
    }

    $mapping = $this->context->getObject();
    assert($mapping instanceof Mapping);
    assert(in_array('langcode', $mapping->getValidKeys(), TRUE));

    $is_translatable = $mapping->hasTranslatableElements();

    if ($is_translatable && !array_key_exists('langcode', $value)) {
      $this->context->buildViolation($constraint->missingMessage)
        ->setParameter('@name', $mapping->getName())
        ->addViolation();
    }
    elseif (!$is_translatable && array_key_exists('langcode', $value)) {
      // phpcs:disable
      @trigger_error(str_replace('@name', $mapping->getName(), $constraint->superfluousMessage), E_USER_DEPRECATED);
      // @todo Convert this deprecation to an error in Drupal 11.
      // $this->context->buildViolation($constraint->superfluousMessage)->addViolation();
      // phpcs:enable
    }
  }

}
