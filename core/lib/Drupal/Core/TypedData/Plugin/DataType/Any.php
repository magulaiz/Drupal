<?php

namespace Drupal\Core\TypedData\Plugin\DataType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\Attribute\DataType;
use Drupal\Core\TypedData\TypedData;

/**
 * The "any" data type.
 *
 * The "any" data type does not implement a list or complex data interface, nor
 * is it mappable to any primitive type. Thus, it may contain any PHP data for
 * which no further metadata is available.
 */
#[DataType(
  id: "any",
  label: new TranslatableMarkup("Any data")
)]
class Any extends TypedData {

  /**
   * The data value.
   *
   * @var mixed
   */
  protected $value;

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($value, $notify = TRUE) {
    $this->value = $value;
    // Notify the parent of any changes.
    if ($notify && isset($this->parent)) {
      $this->parent->onChange($this->name);
    }
  }

}
