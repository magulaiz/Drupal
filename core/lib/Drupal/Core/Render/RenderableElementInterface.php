<?php

namespace Drupal\Core\Render;

/**
 * Defines an object which can be rendered by the Render API.
 * @internal
 */
interface RenderableElementInterface extends \IteratorAggregate, \ArrayAccess {

  /**
   * Sets all element data from a render array.
   *
   * This is a helper for backwards-compatability.
   *
   * @param mixed $data
   *   A render array.
   *
   * @return $this
   */
  public function fromArray($data);

  /**
   * Get a copy of the render element's internal array.
   *
   * This is a helper for backwards-compatability.
   *
   * @return mixed
   *   A render array.
   */
  public function toArray();

}
