<?php

namespace Drupal\Core\Render\Hypermedia\Operations;

use Drupal\Core\Render\Hypermedia\HtmxInterface;

/**
 * Replaces the target element with the element.
 */
class Replace implements HtmxRequestOperationInterface {
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
    $htmx->attributes()->swap('outerHTML');
  }

}
