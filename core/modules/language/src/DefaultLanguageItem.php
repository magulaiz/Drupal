<?php

namespace Drupal\language;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\LanguageItem;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\language\Entity\ContentLanguageSettings;

/**
 * Alternative plugin implementation of the 'language' field type.
 *
 * Replaces the Core 'language' entity field type implementation, changes the
 * default values used.
 *
 * Required settings are:
 *  - target_type: The entity type to reference.
 *
 * @see language_field_info_alter().
 */
class DefaultLanguageItem extends LanguageItem {

  /**
   * {@inheritdoc}
   */
  public function applyDefaultValue($notify = TRUE) {
    // Default to LANGCODE_NOT_SPECIFIED.
    $langcode = Language::LANGCODE_NOT_SPECIFIED;
    if ($entity = $this->getEntity()) {
      $langcode = $this->getDefaultLangcode($entity);
    }
    // Always notify otherwise default langcode will not be set correctly.
    $this->setValue(['value' => $langcode], TRUE);
    return $this;
  }

  /**
   * Provides default language code of given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity whose language code to be loaded.
   *
   * @return string
   *   A string language code.
   */
  public function getDefaultLangcode(EntityInterface $entity) {
    return static::language_get_default_langcode($entity->getEntityTypeId(), $entity->bundle());
  }

  /**
   * Returns the default language code assigned to an entity type and a bundle.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle name.
   *
   * @return string
   *   The language code.
   */
  public static function language_get_default_langcode($entity_type, $bundle) {
    $configuration = ContentLanguageSettings::loadByEntityTypeBundle($entity_type, $bundle);

    $default_value = NULL;
    $language_interface = \Drupal::languageManager()->getCurrentLanguage();
    switch ($configuration->getDefaultLangcode()) {
      case LanguageInterface::LANGCODE_SITE_DEFAULT:
        $default_value = \Drupal::languageManager()->getDefaultLanguage()->getId();
        break;

      case 'current_interface':
        $default_value = $language_interface->getId();
        break;

      case 'authors_default':
        $user = \Drupal::currentUser();
        $language_code = $user->getPreferredLangcode();
        if (!empty($language_code)) {
          $default_value = $language_code;
        }
        else {
          $default_value = $language_interface->getId();
        }
        break;
    }
    if ($default_value) {
      return $default_value;
    }

    // If we still do not have a default value, just return the value stored in
    // the configuration; it has to be an actual language code.
    return $configuration->getDefaultLangcode();
  }

}
