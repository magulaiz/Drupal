<?php

namespace Drupal\form_test;

/**
 * Test enum used in select/checkboxes and radio tests.
 */
enum FormTestIntEnum: int {

  case NONE = 0;
  case FEW = 3;
  case MANY = 12;
  case ALMOST_ALL = 99999;

}
