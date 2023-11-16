<?php

namespace Drupal\Core\Config\Schema;

use Drupal\Core\TypedData\DataDefinition;

class EnumDataDefinition extends DataDefinition {

  /**
   * {@inheritdoc}
   */
  public function getEnum(): string {
    return $this->definition['enum'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDataType(): string {
    if (!is_a($this->getEnum(), \BackedEnum::class, TRUE)) {
      throw new \InvalidArgumentException("{$this->getEnum()} is not a backed enum");
    }
    return 'enum';
  }

}
