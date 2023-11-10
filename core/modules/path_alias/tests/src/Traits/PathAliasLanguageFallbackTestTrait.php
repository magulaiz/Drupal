<?php

namespace Drupal\Tests\path_alias\Traits;

/**
 * Trait for tests that control fallback language of path alias.
 */
trait PathAliasLanguageFallbackTestTrait {

  /**
   * Sets the fallback language code used for path alias.
   *
   * @param string|null $langcode
   *   The language code or NULL to erase the previously set value.
   */
  protected function setPathAliasFallbackLanguage(string $langcode = NULL) {
    \Drupal::state()
      ->set('path_alias_language_fallback_test.fallback_path_alias_alter.candidates', $langcode);
  }

}
