<?php

namespace Drupal\Core\Render\Element;

/**
 * Provides a generic render element for internal use while rendering.
 *
 * @RenderElement("generic")
 */
class Generic extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return [];
  }

}
