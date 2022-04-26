<?php

declare(strict_types = 1);

namespace Drupal\ckeditor5\Plugin\Validation\Constraint;

use Drupal\ckeditor5\HTMLRestrictions;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Ensures Source Editing cannot be configured to allow self-XSS.
 *
 * @internal
 */
class SourceEditingPreventSelfXssConstraintValidator extends ConstraintValidator {

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

    $restrictions = HTMLRestrictions::fromString($value);
    // @todo Remove this early return in
    //   https://www.drupal.org/project/drupal/issues/2820364. It is only
    //   necessary because CKEditor5ElementConstraintValidator does not run
    //   before this, which means that this validator cannot assume it receives
    //   valid values.
    if ($restrictions->allowsNothing() || count($restrictions->getAllowedElements()) > 1) {
      return;
    }

    // This validation constraint only validates attributes, not tags; so if all
    // attributes are allowed (TRUE) or no attributes are allowed (FALSE),
    // return early. Only proceed when some attributes are allowed (an array).
    $tags = array_keys($restrictions->getAllowedElements(FALSE));
    $tag = reset($tags);
    $attribute_restrictions = $restrictions->getAllowedElements()[$tag];
    if (!is_array($attribute_restrictions)) {
      return;
    }

    foreach (array_keys($attribute_restrictions) as $attribute_name) {
      // Self-XSS via `on*` attributes.
      if (preg_match('/^on.*$/', $attribute_name) === 1) {
        $this->context->buildViolation($constraint->onAttributeMessage)
          ->setParameter('%dangerous_tag', $value)
          ->addViolation();
      }

      // Self-XSS via `style` attribute.
      if ($attribute_name === 'style') {
        $this->context->buildViolation($constraint->styleAttributeMessage)
          ->setParameter('%dangerous_tag', $value)
          ->addViolation();
      }
    }
  }

}
