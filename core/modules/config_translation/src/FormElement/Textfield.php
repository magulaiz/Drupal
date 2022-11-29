<?php

namespace Drupal\config_translation\FormElement;

use Drupal\Core\Language\LanguageInterface;

/**
 * Defines the text element for the configuration translation interface.
 */
class Textfield extends FormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getTranslationElement(LanguageInterface $translation_language, $source_config, $translation_config) {
    return [
      '#type' => 'text',
    ] + parent::getTranslationElement($translation_language, $source_config, $translation_config);
  }

}
