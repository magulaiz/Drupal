<?php

namespace Drupal\language\Form;

use Drupal\Core\Security\Attribute\TrustedCallback;

class LanguageFormCallbacks {

  /**
   * Implements #process callback for language_element_info_alter()
   *
   * Expands the language_configuration form element.
   */
  #[TrustedCallback]
  public static function LanguageSelectProcess(array &$element) {
    // Don't set the options if another module (translation for example) already
    // set the options.
    if (!isset($element['#options'])) {
      $element['#options'] = [];
      foreach (\Drupal::languageManager()->getLanguages($element['#languages']) as $langcode => $language) {
        $element['#options'][$langcode] = $language->isLocked() ? t('- @name -', ['@name' => $language->getName()]) : $language->getName();
      }
    }
    return $element;
  }

}
