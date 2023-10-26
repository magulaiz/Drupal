<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\Config\Schema\ArrayElement;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\ListInterface;
use Drupal\Core\TypedData\Validation\TypedDataAwareValidatorTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotNullValidator;

/**
 * NotNull constraint validator.
 *
 * Overrides the symfony validator to handle empty Typed Data structures.
 */
class NotNullConstraintValidator extends NotNullValidator {

  use TypedDataAwareValidatorTrait;

  /**
   * {@inheritdoc}
   *
   * phpcs:ignore Drupal.Commenting.FunctionComment.VoidReturn
   * @return void
   */
  public function validate($value, Constraint $constraint) {
    $typed_data = $this->getTypedData();
    // TRICKY: configuration schema's Mapping and Sequence types both extend
    // ArrayElement which in turn implements ComplexDataInterface. But
    // configuration schema distinguishes between empty sequences/mappings on
    // the one hand and NULL on the other. Therefore do not cast empty arrays
    // to NULL for configuration schema ArrayElement implementations.
    if (($typed_data instanceof ListInterface || $typed_data instanceof ComplexDataInterface) && !$typed_data instanceof ArrayElement && $typed_data->isEmpty()) {
      $value = NULL;
    }
    parent::validate($value, $constraint);
  }

}
