<?php

namespace Drupal\big_pipe\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for big_pipe.
 */
class BigPipeHooks {

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_big_pipe_interface_preview')]
  public function themeSuggestionsBigPipeInterfacePreview() : array {
    return [
      'big_pipe_interface_preview' => [
        'variables' => [
          'callback' => NULL,
          'arguments' => NULL,
          'preview' => NULL,
        ],
      ],
    ];
  }

}
