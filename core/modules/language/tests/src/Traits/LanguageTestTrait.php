<?php

namespace Drupal\Tests\language\Traits;

use Drupal\Core\Language\LanguageInterface;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\language\Entity\ContentLanguageSettings;

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

  /**
   * Enables translations for the given entity type bundle.
   *
   * @param string $entity_type_id
   *   ID of the entity type.
   * @param string $bundle
   *   Bundle name.
   * @param string $default_langcode
   *   The language code to use as default language.
   *
   * @return \Drupal\language\ContentLanguageSettingsInterface
   *   The content language config entity saved to enable the bundle
   *   translation.
   */
  public static function enableBundleTranslation(string $entity_type_id, string $bundle, ?string $default_langcode = LanguageInterface::LANGCODE_SITE_DEFAULT) {
    return ContentLanguageSettings::loadByEntityTypeBundle($entity_type_id, $bundle)
      ->setDefaultLangcode($default_langcode)
      ->setLanguageAlterable(TRUE)
      ->save();
  }

}
