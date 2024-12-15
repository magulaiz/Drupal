<?php

namespace Drupal\Core\Render\Hypermedia\Operations;

use Drupal\Core\Render\Hypermedia\HtmxInterface;

/**
 * An HtmxOperation adds attributes or headers to an HTMX object.
 *
 */
interface HtmxOperationInterface {

  /**
   * Add the necessary values to the Htmx object to implement the operation.
   *
   * @param \Drupal\Core\Render\Hypermedia\HtmxInterface $htmx
   *   The Htmx object collecting the attributes and headers.
   */
  public function setProperties(HtmxInterface $htmx): void;

}
