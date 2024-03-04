<?php

namespace Drupal\Core\TypedData;

/**
 * Base class for primitive data types.
 */
abstract class PrimitiveBase extends TypedData implements PrimitiveInterface {

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

}
