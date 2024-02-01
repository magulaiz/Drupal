<?php

namespace Drupal\Core\TypedData\Plugin\DataType;

use Drupal\Core\Serialization\Attribute\JsonSchema;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\Attribute\DataType;
use Drupal\Core\TypedData\PrimitiveBase;
use Drupal\Core\TypedData\Type\BooleanInterface;

/**
 * The boolean data type.
 *
 * The plain value of a boolean is a regular PHP boolean. For setting the value
 * any PHP variable that casts to a boolean may be passed.
 */
#[DataType(
  id: "boolean",
  label: new TranslatableMarkup("Boolean")
)]
class BooleanData extends PrimitiveBase implements BooleanInterface {

  /**
   * {@inheritdoc}
   */
  #[JsonSchema(['type' => 'boolean'])]
  public function getCastedValue() {
    // Special handling for `TRUE` as a string: "TRUE", "True", "true". Same for
    // `FALSE`.
    if (strtoupper($this->value) === 'TRUE') {
      return TRUE;
    }
    if ($this->value === '' || strtoupper($this->value) === 'FALSE') {
      return FALSE;
    }

    return (bool) $this->value;
  }

}
