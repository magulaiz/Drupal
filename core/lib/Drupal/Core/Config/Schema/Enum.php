<?php

namespace Drupal\Core\Config\Schema;

use Drupal\Core\TypedData\TypedData;

class Enum extends TypedData {

  protected string|int $value;

  public function setValue($value, $notify = TRUE): void {
    // Value may be either as enum case or as scalar (string, integer) but we
    // always store it as scalar. For instance, just before a config is saved,
    // the enum case is converted to scalar in order to be stored. But after
    // saving, the configuration schema is checked and this method is called
    // again, but this time $value is a scalar.
    assert($value instanceof \BackedEnum || is_int($value) || is_string($value));
    $value = is_scalar($value) ? $value : $value->value;
    parent::setValue($value, $notify);
  }

  public function getValue(): \BackedEnum|null {
    $enum = $this->getDataDefinition()->getEnum();
    return call_user_func([$enum, 'tryFrom'], parent::getValue());
  }

  public function getScalarValue(): string|int {
    return $this->value;
  }

}
