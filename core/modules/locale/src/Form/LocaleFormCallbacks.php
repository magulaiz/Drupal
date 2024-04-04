<?php

namespace Drupal\locale\Form;

use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Provides trusted callbacks for locale module.
 */
class LocaleFormCallbacks {

  /**
   * Form element #after_build callback: changes to the language update table.
   *
   * Adds labels to the languages and removes checkboxes from languages from
   * which translation files could not be found.
   */
  #[TrustedCallback]
  public static function translationLanguageTable(array $form_element) {
    // Remove checkboxes of languages without updates.
    if ($form_element['#not_found']) {
      foreach ($form_element['#not_found'] as $langcode) {
        $form_element[$langcode] = [];
      }
    }
    return $form_element;
  }

}
