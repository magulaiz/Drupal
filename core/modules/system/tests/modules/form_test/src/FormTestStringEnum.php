<?php

namespace Drupal\form_test;

/**
 * Test enum used in select/checkboxes and radio tests.
 */
enum FormTestStringEnum: string {

  case Hearts = 'H';
  case Diamonds = 'D';
  case Clubs = 'C';
  case Spades = 'S';

}
