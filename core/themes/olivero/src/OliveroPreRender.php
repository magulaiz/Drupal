<?php

namespace Drupal\olivero;

use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Implements trusted prerender callbacks for the Olivero theme.
 *
 * @internal
 */
class OliveroPreRender {

  /**
   * Prerender callback for text_format elements.
   */
  #[TrustedCallback]
  public static function textFormat($element) {
    $element['format']['#attributes']['class'][] = 'filter-wrapper';
    $element['format']['format']['#wrapper_attributes']['class'][] = 'form-item--editor-format';
    $element['format']['format']['#attributes']['class'][] = 'filter-list';
    $element['format']['format']['#attributes']['class'][] = 'form-element--small';
    $element['format']['format']['#attributes']['class'][] = 'form-element--editor-format';
    $element['format']['guidelines']['#attributes']['class'][] = 'filter-guidelines';
    $element['format']['help']['#attributes']['class'][] = 'filter-help';
    return $element;
  }

  /**
   * Prerender callback for status_messages placeholder.
   *
   * @param array $element
   *   A renderable array.
   *
   * @return array
   *   The updated renderable array containing the placeholder.
   */
  #[TrustedCallback]
  public static function messagePlaceholder(array $element) {
    if (isset($element['fallback']['#markup'])) {
      $element['fallback']['#markup'] = '<div data-drupal-messages-fallback class="hidden messages-list"></div>';
    }
    return $element;
  }

}
