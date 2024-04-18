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
   * @param \UnitEnum|null $value
   *   The value.
   * @param bool $notify
   *   (optional) Whether to notify the parent object of the change. Defaults to
   *   TRUE. If a property is updated from a parent object, set it to FALSE to
   *   avoid being notified again.
   */
  public function setValue($value, $notify = TRUE) {
    if (isset($value)) {
      // @todo I think this should be a constraint.
      $class = $this->getDataDefinition()['enum_class'] ?? '';
      if (empty($class)) {
        throw new \InvalidArgumentException('Enum data definitions must supply an "enum_class".');
      }
      // Ensure the value is of the provided class.
      if (!$value instanceof $class) {
        throw new \InvalidArgumentException(sprintf('Invalid value given. Value must be a "%s" but has "%s" instead.', $class, get_class($value)));
      }
      if (!$value instanceof \UnitEnum) {
        throw new \InvalidArgumentException('Invalid value given. Value must be an enum.');
      }
    }
    parent::setValue($value, $notify);
  }

}
