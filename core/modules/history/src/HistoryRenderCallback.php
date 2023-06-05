<?php

namespace Drupal\history;

use Drupal\Core\Render\Element\RenderCallbackInterface;

/**
 * Render callback object.
 */
class HistoryRenderCallback implements RenderCallbackInterface {

  /**
   * #lazy_builder callback; attaches the last read timestamp for a node.
   *
   * @param int $node_id
   *   The node ID for which to attach the last read timestamp.
   *
   * @return array
   *   A renderable array containing the last read timestamp.
   */
  public static function lazyBuilder($node_id) {
    $element = [];
    $timestamps = \Drupal::service('history.repository')->getTimes('node', [$node_id], NULL, 0);
    $element['#attached']['drupalSettings']['history']['lastReadTimestamps'][$node_id] = $timestamps[$node_id];
    return $element;
  }

}
