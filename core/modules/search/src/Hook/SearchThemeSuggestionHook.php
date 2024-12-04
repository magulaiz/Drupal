<?php

namespace Drupal\search\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for search.
 */
class SearchThemeSuggestionHook {

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_search_result')]
  public function themeSuggestionsSearchResult(array $variables): array {
    return ['search_result__' . $variables['plugin_id']];
  }

}
