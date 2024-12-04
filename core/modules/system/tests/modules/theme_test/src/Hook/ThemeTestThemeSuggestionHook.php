<?php

declare(strict_types=1);

namespace Drupal\theme_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for theme_test.
 */
class ThemeTestThemeSuggestionHook {

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_theme_test_preprocess_suggestions')]
  public function themeSuggestionsThemeTestPreprocessSuggestions($variables): array {
    return ['theme_test_preprocess_suggestions__' . $variables['foo']];
  }

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_theme_test_suggestion_provided')]
  public function themeSuggestionsThemeTestSuggestionProvided(array $variables): array {
    return ['theme_test_suggestion_provided__foo'];
  }

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_node')]
  public function themeSuggestionsNode(array $variables): array {
    $xss = '<script type="text/javascript">alert(\'yo\');</script>';
    $suggestions[] = 'node__' . $xss;

    return $suggestions;
  }

}
