<?php

namespace Drupal\Core\Render\Hypermedia\Operations;

use Drupal\Core\Render\Hypermedia\HtmxInterface;

/**
 * Inserts the selected element as the last child of the target element.
 */
class Insert implements HtmxRequestOperationInterface {
  use HtmxRequestTrait;
  use HtmxSelectTrait;
  use HtmxTargetTrait;

  /**
   * {@inheritdoc}
   */
  public function setProperties(HtmxInterface $htmx): void {
    $this->configureRequest($htmx);
    $htmx->attributes()->select($this->select);
    $htmx->attributes()->target($this->target);
    $htmx->attributes()->swap('beforeend');
  }

}
