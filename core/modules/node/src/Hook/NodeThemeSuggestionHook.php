<?php

namespace Drupal\node\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Theme suggestions for node.
 */
class NodeThemeSuggestionHook {

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_node')]
  public function themeSuggestionsNode(array $variables): array {
    $suggestions = [];
    $node = $variables['elements']['#node'];
    $sanitized_view_mode = strtr($variables['elements']['#view_mode'], '.', '_');

    $suggestions[] = 'node__' . $sanitized_view_mode;
    $suggestions[] = 'node__' . $node->bundle();
    $suggestions[] = 'node__' . $node->bundle() . '__' . $sanitized_view_mode;
    $suggestions[] = 'node__' . $node->id();
    $suggestions[] = 'node__' . $node->id() . '__' . $sanitized_view_mode;

    return $suggestions;
  }

}
