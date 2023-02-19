<?php

namespace Drupal\Core\Render\Element;

/**
 * Provides a default render element for internal use while rendering.
 *
 * @RenderElement("default")
 * @internal
 */
class Default extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return [];
  }

}
