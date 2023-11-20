<?php

namespace Drupal\Tests\content_translation\Traits;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Tests\language\Traits\LanguageTestTrait;


/**
 * Helper functions around content translation.
 */
trait ContentTranslationTestTrait {

  use LanguageTestTrait;

  /**
   * Enables content translation for the given entity type bundle.
   *
   * @param string $entity_type_id
   *   ID of the entity type.
   * @param string $bundle
   *   Bundle name.
   * @param string $default_langcode
   *   The language code to use as default language.
   *
   * @return null
   */
  public function enableContentTranslation(string $entity_type_id, string $bundle, ?string $default_langcode = LanguageInterface::LANGCODE_SITE_DEFAULT) {
    self::enableBundleTranslation($entity_type_id, $bundle, $default_langcode);
    $content_translation_manager = $this->container->get('content_translation.manager');
    $content_translation_manager->setEnabled($entity_type_id, $bundle, TRUE);
    $content_translation_manager->setBundleTranslationSettings($entity_type_id, $bundle, [
      'untranslatable_fields_hide' => FALSE,
    ]);
  }

}
