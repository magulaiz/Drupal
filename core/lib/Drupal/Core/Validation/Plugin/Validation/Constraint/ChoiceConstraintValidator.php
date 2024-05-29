<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\OptionsProviderInterface;
use Drupal\Core\TypedData\PrimitiveInterface;
use Drupal\Core\TypedData\Validation\TypedDataAwareValidatorTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ChoiceValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

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

    $typed_data = $this->getTypedData();
    if ($typed_data instanceof OptionsProviderInterface) {
      $allowed_values = $typed_data->getSettableValues();
      $constraint->choices = $allowed_values;

      // If the data is complex, we have to validate its main property.
      if ($typed_data instanceof ComplexDataInterface) {
        $name = $typed_data->getDataDefinition()->getMainPropertyName();
        if (!isset($name)) {
          throw new \LogicException('Cannot validate allowed values for complex data without a main property.');
        }
        $typed_data = $typed_data->get($name);
        $value = $typed_data->getValue();
      }
    }

    // The parent implementation ignores values that are not set, but ensures
    // some choices are available. However, we want to support empty choices
    // for undefined values; for instance, if a term reference field points to
    // an empty vocabulary.
    if (!isset($value)) {
      return;
    }

    // Get the value with the proper datatype in order to make strict
    // comparisons using in_array().
    if (!($typed_data instanceof PrimitiveInterface)) {
      throw new \LogicException('The data type must be a PrimitiveInterface at this point.');
    }
    $value = $typed_data->getCastedValue();

    // We could set a constraint callback to use the OptionsProviderInterface,
    // but this is not possible right now because we do the typecasting
    // further down.
    if ($constraint->callback) {
      if (!\is_callable($choices = [$this->context->getObject(), $constraint->callback])
        && !\is_callable($choices = [$this->context->getClassName(), $constraint->callback])
        && !\is_callable($choices = $constraint->callback)
      ) {
        throw new ConstraintDefinitionException('The AllowedValuesConstraint constraint expects a valid callback');
      }
      $allowed_values = \call_user_func($choices);
      $constraint->choices = $allowed_values;
      // parent::validate() does not need to invoke the callback again.
      $constraint->callback = NULL;
    }

    parent::validate($value, $constraint);
  }

}
