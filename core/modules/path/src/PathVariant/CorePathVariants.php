<?php

declare(strict_types=1);

namespace Drupal\path\PathVariant;

use Drupal\path\Enum\PathVariantEnumInterface;

/**
 * Represents static path variants provided by core.
 */
enum CorePathVariants implements PathVariantEnumInterface {

  case Default;

  public function getMachineName(): string {
    return match ($this) {
      static::Default => PathVariant::DEFAULT,
    };
  }

}
