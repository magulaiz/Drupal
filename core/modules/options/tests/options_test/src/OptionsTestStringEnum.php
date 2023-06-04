<?php

namespace Drupal\options_test;

/**
 * Test enum used in options field tests.
 */
enum OptionsTestStringEnum: string {

  case Hearts = 'H';
  case Diamonds = 'D';
  case Clubs = 'C';
  case Spades = 'S';

}
