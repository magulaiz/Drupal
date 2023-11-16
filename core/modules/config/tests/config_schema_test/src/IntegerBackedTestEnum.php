<?php

namespace Drupal\config_schema_test;

/**
 * Testing integer backed enum.
 */
enum IntegerBackedTestEnum: int {

  case NORMAL = 0;
  case MAJOR = 1;
  case CRITICAL = 2;

}
