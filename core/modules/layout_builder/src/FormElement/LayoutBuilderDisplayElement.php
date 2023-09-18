<?php

namespace Drupal\layout_builder\FormElement;

use Drupal\config_translation\FormElement\ListElement;
use Drupal\Core\Language\LanguageInterface;

/**
 *
 */
class LayoutBuilderDisplayElement extends ListElement {

  /**
   * {@inheritdoc}
   */
  public function getTranslationBuild(
    LanguageInterface $source_language,
    LanguageInterface $translation_language,
    $source_config,
    $translation_config,
    array $parents,
    $base_key = NULL
  ) {
    $parent_build = parent::getTranslationBuild($source_language, $translation_language,
      $source_config, $translation_config, $parents,
      $base_key);
    $var = 1;
  }

}
