<?php

namespace Drupal\Tests\Component\Serialization;

enum BackedEnumValue: string {
  case Yes = 'yes';
  case No = 'no';
  case Maybe = 'maybe';
}
