<?php

namespace Drupal\Core\TypedData\Plugin\DataType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\Attribute\DataType;
use Drupal\Core\TypedData\TypedData;

/**
 * The "enum" data type.
 *
 * @ingroup typed_data
 */
#[DataType(
  id: "enum",
  label: new TranslatableMarkup("Enum"),
)]
class Enum extends TypedData {

  /**
   * The data value.
   */
  protected \UnitEnum $value;

  /**
   * Overrides \Drupal\Core\TypedData\TypedData::setValue().
   *
   * @param array|null $value
   *   The value.
   * @param bool $notify
   *   (optional) Whether to notify the parent object of the change. Defaults to
   *   TRUE. If a property is updated from a parent object, set it to FALSE to
   *   avoid being notified again.
   */
  public function setValue($value, $notify = TRUE) {
    if (isset($value) && !($value instanceof \UnitEnum)) {
      throw new \InvalidArgumentException("Invalid value given. Value must be an enum.");
    }
    parent::setValue($value, $notify);
  }

}
