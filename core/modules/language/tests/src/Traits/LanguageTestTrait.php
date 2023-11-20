<?php

namespace Drupal\Tests\language\Traits;

use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Helper functions around language.
 */
trait LanguageTestTrait {

  /**
   * Creates a configurable language object from a langcode.
   *
   * @param string $langcode
   *   The language code to use to create the object.
   *
   * @return \Drupal\Core\Language\ConfigurableLanguageInterface
   *   Created language.
   *
   * @see \Drupal\Core\Language\LanguageManager::getStandardLanguageList()
   */
  public static function createLanguageFromLangcode(string $langcode) {
    return ConfigurableLanguage::createFromLangcode($langcode)
      ->save();
  }

}
