<?php

namespace Drupal\Tests\language\Traits;

use Drupal\Core\Language\LanguageInterface;
use Drupal\field\Entity\FieldConfig;
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
   *   The saved content language config entity.
   */
  public static function enableBundleTranslation(string $entity_type_id, string $bundle, ?string $default_langcode = LanguageInterface::LANGCODE_SITE_DEFAULT) {
    return ContentLanguageSettings::loadByEntityTypeBundle($entity_type_id, $bundle)
      ->setDefaultLangcode($default_langcode)
      ->setLanguageAlterable(TRUE)
      ->save();
  }

  /**
   * Disables translations for the given entity type bundle.
   *
   * @param string $entity_type_id
   *   ID of the entity type.
   * @param string $bundle
   *   Bundle name.
   */
  public static function disableBundleTranslation(string $entity_type_id, string $bundle) {
    // TODO Why are both a save and a delete needed?
    $content_language_settings = ContentLanguageSettings::loadByEntityTypeBundle($entity_type_id, $bundle);
    $content_language_settings->setLanguageAlterable(FALSE)
      ->save();
    $content_language_settings->delete();
  }

  /**
   * Set and save a given field instance translation status.
   *
   * @param string $entity_type_id
   *   ID of the entity type.
   * @param string $bundle
   *   Bundle name.
   * @param string $field_name
   *   Name of the field.
   * @param bool $status
   *   Whether the field should be translatable or not.
   *
   * @return null
   */
  public static function setFieldTranslatable(string $entity_type_id, string $bundle, string $field_name, bool $status) {
    FieldConfig::loadByName($entity_type_id, $bundle, $field_name)
      ->setTranslatable($status)
      ->save();
  }

}
