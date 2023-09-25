<?php

namespace Drupal\Component\Transliteration;

/**
 * Remove diacritics (accents).
 */
interface RemoveDiacriticsInterface {

  /**
   * Removes diacritics (accents) from a string.
   *
   * @param string $string
   *   A string of UTF-8 encoded characters.
   *
   * @return string
   *   The same string with diacritics (accents) stripped.
   */
  public function removeDiacritics(string $string): string;

}
