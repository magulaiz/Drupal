<?php

declare(strict_types=1);

namespace Drupal\Tests\Component\Serialization;

enum BackedEnumValue: string {
  case Yes = 'yes';
  case No = 'no';
  case Maybe = 'maybe';
}
