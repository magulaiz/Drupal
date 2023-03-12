<?php

namespace Drupal\Core\TypedData;

/**
 * Base class for primitive data types.
 */
abstract class PrimitiveBase extends TypedData implements PrimitiveInterface {

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
