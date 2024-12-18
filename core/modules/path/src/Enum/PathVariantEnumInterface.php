<?php

declare(strict_types=1);

namespace Drupal\path\Enum;

interface PathVariantEnumInterface extends \UnitEnum {

  /**
   * The machine name of a path variant.
   *
   * This is used in the PathAlias entities' `variant` field.
   */
  public function getMachineName(): string;

}
