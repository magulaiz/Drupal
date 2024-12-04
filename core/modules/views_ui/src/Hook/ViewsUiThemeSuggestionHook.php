<?php

namespace Drupal\views_ui\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for views_ui.
 */
class ViewsUiThemeSuggestionHook {

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_views_ui_view_preview_section')]
  public function themeSuggestionsViewsUiViewPreviewSection(array $variables): array {
    return ['views_ui_view_preview_section__' . $variables['section']];
  }

}
