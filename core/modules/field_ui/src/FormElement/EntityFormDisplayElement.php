<?php

namespace Drupal\field_ui\FormElement;

use Drupal\config_translation\FormElement\ListElement;
use Drupal\Core\Language\LanguageInterface;

class EntityFormDisplayElement extends ListElement {

  public function getTranslationBuild(
    LanguageInterface $source_language,
    LanguageInterface $translation_language,
    $source_config,
    $translation_config,
    array $parents,
    $base_key = NULL
  ) {
    // @todo Would this be a way we could get rid of
    //   field_ui_form_config_translation_form_alter() or at least make it
    //   simpler?
    return parent::getTranslationBuild($source_language, $translation_language,
      $source_config, $translation_config, $parents,
      $base_key);
  }

}
