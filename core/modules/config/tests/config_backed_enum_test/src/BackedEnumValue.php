<?php

declare(strict_types=1);

namespace Drupal\config_backed_enum_test;

/**
 * Represents options for a boolean-like value.
 */
enum BackedEnumValue: string {
  case Yes = 'yes';
  case No = 'no';
  case Maybe = 'maybe';
}
