<?php

namespace Drupal\config_schema_test;

/**
 * Testing enum.
 */
enum StringBackedTestEnum: string {

  case APPLE = 'apple';
  case PINEAPPLE = 'pineapple';
  case PLUM = 'plum';

}
